<?php

namespace Tests\Feature\Facturacion;

use App\Services\Cobranzas\FacturacionRecibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class RegistrarFacturacionCommandTest extends FacturacionTestCase
{
    public function test_apagado_no_procesa_nada(): void
    {
        config(['services.cobranzas.enabled' => false]);
        Http::fake();
        $recibo = $this->crearRecibo();

        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(PaymentStatus::Pagado, $recibo->fresh()->status);
    }

    public function test_factura_los_pagados_y_reintenta_los_errores_bajo_el_tope(): void
    {
        $this->fakeGatewayFacturaOk();
        $pagado = $this->crearRecibo();
        $reintentable = $this->crearRecibo([
            'status' => PaymentStatus::ErrorFacturacion,
            'facturacion_intentos' => FacturacionRecibo::MAX_INTENTOS_AUTOMATICOS - 1,
            'facturacion_error' => 'fallo anterior',
        ]);

        $this->artisan('pagos:registrar-facturacion')
            ->expectsOutput('Recibos procesados: 2')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Facturado, $pagado->fresh()->status);
        $this->assertSame(PaymentStatus::Facturado, $reintentable->fresh()->status);
    }

    public function test_ignora_los_que_no_corresponden(): void
    {
        Http::fake();
        $agotado = $this->crearRecibo([
            'status' => PaymentStatus::ErrorFacturacion,
            'facturacion_intentos' => FacturacionRecibo::MAX_INTENTOS_AUTOMATICOS,
        ]);
        $sinDetalle = $this->crearRecibo(['debt_items' => null]);
        $pendiente = $this->crearRecibo(['status' => PaymentStatus::Pendiente, 'paid_at' => null]);
        $yaFacturado = $this->crearRecibo(['status' => PaymentStatus::Facturado]);

        $this->artisan('pagos:registrar-facturacion')
            ->doesntExpectOutputToContain('Recibos procesados')
            ->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(PaymentStatus::ErrorFacturacion, $agotado->fresh()->status);
        $this->assertSame(PaymentStatus::Pagado, $sinDetalle->fresh()->status);
        $this->assertSame(PaymentStatus::Pendiente, $pendiente->fresh()->status);
        $this->assertSame(PaymentStatus::Facturado, $yaFacturado->fresh()->status);
    }

    public function test_un_recibo_que_falla_no_frena_a_los_demas(): void
    {
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/liquidar/')) {
                return $request['alias'] === 'MALO'
                    ? Http::response(['estado' => 'ERROR', 'error' => 'rechazado'])
                    : Http::response(['estado' => 'FACTURADO', 'cobranzas_uuid' => 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee']);
            }

            return Http::response(self::PDF_FALSO);
        });
        $malo = $this->crearRecibo(['alias' => 'MALO', 'paid_at' => now()->subHour()]);
        $bueno = $this->crearRecibo(['alias' => 'BUENO']);

        $this->artisan('pagos:registrar-facturacion')->assertSuccessful();

        $this->assertSame(PaymentStatus::ErrorFacturacion, $malo->fresh()->status);
        $this->assertSame(1, $malo->fresh()->facturacion_intentos);
        $this->assertSame(PaymentStatus::Facturado, $bueno->fresh()->status);
    }

    public function test_procesa_como_maximo_20_pagados_por_corrida_empezando_por_el_mas_viejo(): void
    {
        $this->fakeGatewayFacturaOk();
        $recibos = collect(range(1, 21))->map(fn ($i) => $this->crearRecibo([
            'paid_at' => now()->subMinutes(100 - $i),
        ]));

        $this->artisan('pagos:registrar-facturacion')
            ->expectsOutput('Recibos procesados: 20')
            ->assertSuccessful();

        $this->assertSame(PaymentStatus::Facturado, $recibos->first()->fresh()->status);
        $this->assertSame(PaymentStatus::Pagado, $recibos->last()->fresh()->status);
    }
}
