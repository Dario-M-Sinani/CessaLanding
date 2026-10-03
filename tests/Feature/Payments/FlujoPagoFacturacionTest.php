<?php

namespace Tests\Feature\Payments;

use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Cadena COMPLETA de un pago real, de punta a punta con todo falseado (nunca toca bancos ni el
 * gateway reales): el cliente paga -> el banco notifica (callback) -> el Recibo queda Pagado ->
 * el cron pagos:registrar-facturacion lo factura por el gateway -> queda Facturado con su PDF.
 * Cubre SIP (Banco BISA) y BNB, y que reintentos de notificación / corridas del cron no
 * dupliquen la facturación. Es la prueba que da certeza de que un pago real no se rompe en el
 * camino.
 */
class FlujoPagoFacturacionTest extends PaymentsTestCase
{
    private const GATEWAY = 'http://gateway.test';

    private const PDF = '%PDF-1.4 comprobante';

    private const SIP_AUTH = ['Authorization' => 'Basic c2lwLXVzZXI6c2lwLXBhc3M=']; // sip-user:sip-pass

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.cobranzas.enabled' => true,
            'services.cobranzas.gateway_base_url' => self::GATEWAY,
            'services.cobranzas.gateway_api_key' => 'clave-gw',
        ]);

        Storage::fake('public');
    }

    /** Stubs del gateway propio (cobranza-cessa): liquidar -> FACTURADO, comprobante -> PDF. */
    private function fakeGateway(): array
    {
        return [
            self::GATEWAY.'/api/externo/recibos-web/liquidar/' => Http::response([
                'estado' => 'FACTURADO',
                'cobranzas_uuid' => '11111111-2222-3333-4444-555555555555',
                'error' => '',
            ]),
            self::GATEWAY.'/api/externo/recibos-web/*/comprobante/' => Http::response(self::PDF, 200, ['Content-Type' => 'application/pdf']),
        ];
    }

    private function reciboPendiente(array $over = []): Recibo
    {
        return $this->crearReciboBnb(array_merge([
            'status' => PaymentStatus::Pendiente,
            'qr_image_path' => 'recibos/qr/x.png',
            'debt_items' => [
                ['nro_cliente' => '197596', 'anio' => 2026, 'mes' => 1, 'importe' => 100, 'debito_credito' => 'DEBITO'],
            ],
        ], $over));
    }

    public function test_flujo_sip_pago_a_facturado_de_punta_a_punta(): void
    {
        Http::fake($this->fakeGateway());
        $recibo = $this->reciboPendiente(['provider' => 'sip_bisa', 'alias' => 'CESSA-WEB-FLUJO-SIP']);

        // 1) SIP notifica el pago.
        $this->postJson('/api/pagos/sip/confirmar-pago', [
            'alias' => 'CESSA-WEB-FLUJO-SIP',
            'numeroOrdenOriginante' => 'ORD-1',
            'monto' => '100.00',
            'nombreCliente' => 'JUAN PEREZ',
        ], self::SIP_AUTH)->assertOk()->assertJson(['codigo' => '0000']);

        $this->assertSame(PaymentStatus::Pagado, $recibo->fresh()->status);

        // 2) El cron lo factura.
        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Facturado, $recibo->status);
        $this->assertSame('11111111-2222-3333-4444-555555555555', $recibo->cobranzas_uuid);
        $this->assertNotNull($recibo->facturado_at);
        Storage::disk('public')->assertExists($recibo->comprobante_path);
        $this->assertSame(self::PDF, Storage::disk('public')->get($recibo->comprobante_path));
    }

    public function test_flujo_bnb_pago_a_facturado_de_punta_a_punta(): void
    {
        Http::fake(array_merge($this->fakeToken(), $this->fakeGateway(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 900001, 'statusId' => 2, 'voucherId' => 'VCH-1']),
        ]));
        $recibo = $this->reciboPendiente(['provider' => 'bnb', 'alias' => 'CESSA-WEB-FLUJO-BNB', 'provider_qr_id' => '900001']);

        // 1) BNB notifica (el controller verifica contra el BNB antes de marcar Pagado).
        $this->postJson('/api/pagos/bnb/receive-notification', [
            'QRId' => '900001',
            'originName' => 'MARIA LOPEZ',
            'VoucherId' => 'VCH-1',
            'additionalData' => 'CESSA-WEB-FLUJO-BNB',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame(PaymentStatus::Pagado, $recibo->fresh()->status);

        // 2) El cron lo factura (la facturación es igual para cualquier banco).
        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();

        $this->assertSame(PaymentStatus::Facturado, $recibo->fresh()->status);
        Storage::disk('public')->assertExists($recibo->fresh()->comprobante_path);
    }

    public function test_el_cron_no_refactura_lo_ya_facturado(): void
    {
        Http::fake($this->fakeGateway());
        $this->reciboPendiente(['provider' => 'sip_bisa', 'alias' => 'CESSA-WEB-FLUJO-2', 'status' => PaymentStatus::Pagado, 'paid_at' => now()]);

        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();
        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();

        // El gateway se llamó UNA sola vez a liquidar, aunque el cron corrió dos veces.
        Http::assertSentCount(2); // 1 liquidar + 1 comprobante (la 2da corrida no toca el gateway)
    }

    public function test_doble_notificacion_no_duplica_ni_refactura(): void
    {
        Http::fake($this->fakeGateway());
        $recibo = $this->reciboPendiente(['provider' => 'sip_bisa', 'alias' => 'CESSA-WEB-FLUJO-3']);

        $notificar = fn () => $this->postJson('/api/pagos/sip/confirmar-pago', [
            'alias' => 'CESSA-WEB-FLUJO-3', 'numeroOrdenOriginante' => 'ORD-1', 'monto' => '100.00',
        ], self::SIP_AUTH);

        $notificar()->assertOk();
        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();
        $this->assertSame(PaymentStatus::Facturado, $recibo->fresh()->status);
        $facturadoAt = $recibo->fresh()->facturado_at;

        // Reintento tardío de SIP + otra corrida del cron: nada cambia, no se refactura.
        $notificar()->assertOk();
        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Facturado, $recibo->status);
        $this->assertEquals($facturadoAt, $recibo->facturado_at);
        Http::assertSentCount(2); // sigue siendo 1 liquidar + 1 comprobante
    }
}
