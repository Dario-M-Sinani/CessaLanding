<?php

namespace Tests\Feature\Facturacion;

use App\Services\Cobranzas\FacturacionRecibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FacturacionReciboTest extends FacturacionTestCase
{
    private function procesar($recibo): void
    {
        app(FacturacionRecibo::class)->procesar($recibo);
        $recibo->refresh();
    }

    public function test_recibo_pagado_queda_facturado_con_pdf_guardado(): void
    {
        $this->fakeGatewayFacturaOk('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
        $recibo = $this->crearRecibo(['facturacion_error' => 'error viejo']);

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::Facturado, $recibo->status);
        $this->assertSame('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', $recibo->cobranzas_uuid);
        $this->assertSame("recibos/comprobantes/{$recibo->alias}.pdf", $recibo->comprobante_path);
        $this->assertNotNull($recibo->facturado_at);
        $this->assertNull($recibo->facturacion_error);
        $this->assertSame(0, $recibo->facturacion_intentos);
        Storage::disk('public')->assertExists($recibo->comprobante_path);
        $this->assertSame(self::PDF_FALSO, Storage::disk('public')->get($recibo->comprobante_path));
    }

    public function test_envia_al_gateway_el_snapshot_del_recibo_con_api_key(): void
    {
        $this->fakeGatewayFacturaOk();
        $recibo = $this->crearRecibo(['currency' => 'usd', 'amount' => 20]);

        $this->procesar($recibo);

        Http::assertSent(function (Request $request) use ($recibo) {
            return $request->url() === self::GATEWAY.'/api/externo/recibos-web/liquidar/'
                && $request->method() === 'POST'
                && $request->hasHeader('X-Api-Key', 'clave-de-prueba')
                && $request['alias'] === $recibo->alias
                && $request['nro_cliente'] === '179185'
                && (float) $request['monto'] === 20.0
                && $request['moneda'] === 'USD'
                && $request['detalle'] === $recibo->debt_items
                && $request['numero_orden_originante'] === 'ORD-123'
                && ! empty($request['fecha_pago']);
        });
        Http::assertSent(fn (Request $r) => $r->url() === self::GATEWAY."/api/externo/recibos-web/{$recibo->alias}/comprobante/");
    }

    public function test_moneda_distinta_de_usd_se_envia_como_bob(): void
    {
        $this->fakeGatewayFacturaOk();

        $this->procesar($this->crearRecibo(['currency' => 'bob']));

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/liquidar/') && $r['moneda'] === 'BOB');
    }

    public function test_sin_debt_items_marca_error_sin_llamar_al_gateway(): void
    {
        Http::fake();
        $recibo = $this->crearRecibo(['debt_items' => null]);

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertSame(1, $recibo->facturacion_intentos);
        $this->assertStringContainsString('debt_items', $recibo->facturacion_error);
        Http::assertNothingSent();
    }

    public function test_rechazo_de_negocio_del_gateway_marca_error_y_guarda_uuid(): void
    {
        Http::fake([
            self::GATEWAY.'/api/externo/recibos-web/liquidar/' => Http::response([
                'estado' => 'ERROR',
                'cobranzas_uuid' => 'ffffffff-0000-0000-0000-000000000000',
                'error' => 'CFC510: cliente sin datos de facturación',
            ]),
        ]);
        $recibo = $this->crearRecibo(['facturacion_intentos' => 2]);

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertSame(3, $recibo->facturacion_intentos);
        $this->assertSame('CFC510: cliente sin datos de facturación', $recibo->facturacion_error);
        $this->assertSame('ffffffff-0000-0000-0000-000000000000', $recibo->cobranzas_uuid);
        $this->assertNull($recibo->comprobante_path);
        Http::assertNotSent(fn (Request $r) => str_ends_with($r->url(), '/comprobante/'));
    }

    public function test_502_del_gateway_se_trata_como_rechazo_de_negocio(): void
    {
        Http::fake([
            self::GATEWAY.'/*' => Http::response(['estado' => 'ERROR', 'error' => 'SIIC rechazó el pago'], 502),
        ]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertSame('SIIC rechazó el pago', $recibo->facturacion_error);
    }

    public function test_estado_no_facturado_sin_mensaje_usa_error_generico(): void
    {
        Http::fake([self::GATEWAY.'/*' => Http::response(['estado' => 'PENDIENTE', 'error' => null])]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertStringContainsString('sin detalle de error', $recibo->facturacion_error);
    }

    public function test_no_pisa_un_cobranzas_uuid_ya_guardado(): void
    {
        $this->fakeGatewayFacturaOk('99999999-9999-9999-9999-999999999999');
        $recibo = $this->crearRecibo(['cobranzas_uuid' => '12345678-1234-1234-1234-123456789012']);

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::Facturado, $recibo->status);
        $this->assertSame('12345678-1234-1234-1234-123456789012', $recibo->cobranzas_uuid);
    }

    public function test_api_key_invalida_403_marca_error_de_integracion(): void
    {
        Http::fake([self::GATEWAY.'/*' => Http::response(['detail' => 'API key inválida'], 403)]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertStringContainsString('HTTP 403', $recibo->facturacion_error);
        $this->assertStringContainsString('API key inválida', $recibo->facturacion_error);
    }

    public function test_respuesta_no_json_no_rompe_y_marca_error(): void
    {
        Http::fake([self::GATEWAY.'/*' => Http::response('<html>Bad Gateway</html>', 200)]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertStringContainsString('Bad Gateway', $recibo->facturacion_error);
    }

    public function test_gateway_caido_marca_error_inesperado_sin_tirar_excepcion(): void
    {
        Http::fake([self::GATEWAY.'/*' => Http::failedConnection()]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertStringStartsWith('Error inesperado:', $recibo->facturacion_error);
    }

    public function test_facturado_pero_falla_el_pdf_queda_en_error_con_uuid(): void
    {
        Http::fake([
            self::GATEWAY.'/api/externo/recibos-web/liquidar/' => Http::response([
                'estado' => 'FACTURADO',
                'cobranzas_uuid' => 'abababab-abab-abab-abab-abababababab',
            ]),
            self::GATEWAY.'/api/externo/recibos-web/*/comprobante/' => Http::response(['error' => 'comprobante no disponible'], 404),
        ]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertSame('abababab-abab-abab-abab-abababababab', $recibo->cobranzas_uuid);
        $this->assertStringContainsString('comprobante no disponible', $recibo->facturacion_error);
        $this->assertNull($recibo->comprobante_path);
    }

    public function test_reintento_despues_de_un_error_termina_facturado_y_limpia_el_error(): void
    {
        Http::fakeSequence(self::GATEWAY.'/api/externo/recibos-web/liquidar/')
            ->push(['estado' => 'ERROR', 'error' => 'SIIC no responde'])
            ->push(['estado' => 'FACTURADO', 'cobranzas_uuid' => 'cdcdcdcd-cdcd-cdcd-cdcd-cdcdcdcdcdcd']);
        Http::fake([self::GATEWAY.'/api/externo/recibos-web/*/comprobante/' => Http::response(self::PDF_FALSO)]);
        $recibo = $this->crearRecibo();

        $this->procesar($recibo);
        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);

        $this->procesar($recibo);
        $this->assertSame(PaymentStatus::Facturado, $recibo->status);
        $this->assertNull($recibo->facturacion_error);
        $this->assertSame(1, $recibo->facturacion_intentos);
    }
}
