<?php

namespace Tests\Feature\Payments;

use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Respaldo del callback de SIP: si el aviso de pago no llega, el QR BISA pagado se confirma
 * consultando estadoTransaccion.
 */
class SincronizarPagosSipTest extends PaymentsTestCase
{
    private const SIP = 'http://sip.test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sip.base_url' => self::SIP,
            'services.sip.apikey' => 'k',
            'services.sip.username' => 'u',
            'services.sip.password' => 'p',
            'services.sip.apikey_servicio' => 's',
        ]);
        Cache::forget('sip:auth_token');
    }

    private function estadoSip(string $estado): void
    {
        Http::fake([
            self::SIP.'/autenticacion/v1/generarToken' => Http::response(['codigo' => 'OK', 'objeto' => ['token' => 'T']]),
            self::SIP.'/api/v1/estadoTransaccion' => Http::response(['codigo' => '0000', 'objeto' => [
                'alias' => 'x', 'estadoActual' => $estado, 'numeroOrdenOriginante' => 'ORD-1',
                'nombreCliente' => 'JUAN PEREZ', 'documentoCliente' => '123', 'cuentaCliente' => '999',
            ]]),
        ]);
    }

    private function crearReciboSip(array $atributos = []): Recibo
    {
        static $secuencia = 0;
        $secuencia++;

        return Recibo::create(array_merge([
            'provider' => 'sip_bisa',
            'alias' => "CESSA-WEB-SIP-{$secuencia}",
            'nro_cliente' => '197596',
            'amount' => 100.00,
            'currency' => 'BOB',
            'glosa' => 'Pago de prueba',
            'status' => PaymentStatus::Pendiente,
            'expires_at' => now()->addMinutes(5),
            'qr_image_path' => 'recibos/qr/x.png',
        ], $atributos));
    }

    public function test_pendiente_pagado_sin_callback_queda_pagado(): void
    {
        $recibo = $this->crearReciboSip();
        $this->estadoSip('PAGADO');

        $this->artisan('pagos:sincronizar-sip')->assertSuccessful();

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Pagado, $recibo->status);
        $this->assertSame('ORD-1', $recibo->provider_order_number);
        $this->assertSame('JUAN PEREZ', $recibo->payer_name);
    }

    public function test_expirado_hace_poco_pero_pagado_se_honra(): void
    {
        $recibo = $this->crearReciboSip(['status' => PaymentStatus::Expirado]);
        $this->estadoSip('PAGADO');

        $this->artisan('pagos:sincronizar-sip')->assertSuccessful();

        $this->assertSame(PaymentStatus::Pagado, $recibo->refresh()->status);
    }

    public function test_pendiente_no_pagado_sigue_igual(): void
    {
        $recibo = $this->crearReciboSip();
        $this->estadoSip('PENDIENTE');

        $this->artisan('pagos:sincronizar-sip')->assertSuccessful();

        $this->assertSame(PaymentStatus::Pendiente, $recibo->refresh()->status);
    }

    public function test_no_consulta_viejos_facturados_simulados_ni_bnb(): void
    {
        $viejo = $this->crearReciboSip(['status' => PaymentStatus::Expirado]);
        $viejo->forceFill(['created_at' => now()->subHours(2)])->save();
        $this->crearReciboSip(['status' => PaymentStatus::Facturado]);
        $this->crearReciboSip(['qr_image_path' => null]); // simulado: nunca tuvo QR en SIP
        $this->crearReciboBnb();
        $this->estadoSip('PAGADO');

        $this->artisan('pagos:sincronizar-sip')->assertSuccessful();

        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/estadoTransaccion'));
        $this->assertSame(PaymentStatus::Expirado, $viejo->refresh()->status);
    }

    public function test_error_de_sip_no_corta_el_lote(): void
    {
        $recibo = $this->crearReciboSip();
        Http::fake([
            self::SIP.'/autenticacion/v1/generarToken' => Http::response(['codigo' => 'OK', 'objeto' => ['token' => 'T']]),
            self::SIP.'/api/v1/estadoTransaccion' => Http::response(['codigo' => '9999', 'mensaje' => 'caído'], 500),
        ]);

        $this->artisan('pagos:sincronizar-sip')->assertSuccessful();

        $this->assertSame(PaymentStatus::Pendiente, $recibo->refresh()->status);
    }
}
