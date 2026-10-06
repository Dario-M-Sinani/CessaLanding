<?php

namespace Tests\Feature\Payments;

use App\Http\Controllers\FacturasClienteController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * "Tus últimas facturas" de Consulta de Deuda: solo para la cuenta verificada en la sesión (nro
 * de cliente + N° de Cuenta), y el PDF se pide por índice de la lista guardada en la sesión, nunca
 * con datos que mande el navegador (el PDF trae nombre y CI del cliente).
 */
class FacturasClienteTest extends PaymentsTestCase
{
    private const ITEM = [
        'codigo_sucursal' => '1', 'nro_comprobante' => '3154854', 'nro_suministro' => '1', 'fecha' => '20260813',
        'tipo' => '3', 'letra_comprobante' => '            ', 'nro_autorizacion' => '3', 'nro_cliente' => '197596',
        'importe' => '436.00', 'pago_fecha' => '20260820', 'pago_hora' => '08:20:57', 'detalle' => 'Fact Energia AGOSTO/2026',
    ];

    private function verificado(string $nroCliente = '197596', ?int $at = null): self
    {
        return $this->withSession([FacturasClienteController::SESION_VERIFICADO => ['nro_cliente' => $nroCliente, 'at' => $at ?? now()->timestamp]]);
    }

    private function fakeSiic(): void
    {
        Http::fake([
            '*/v1/clientes/197596/pagos*' => Http::response(['items' => [self::ITEM]]),
            '*/v1/comprobantes' => Http::response('%PDF-1.7 factura', 200, ['Content-Type' => 'application/pdf']),
        ]);
    }

    public function test_lista_las_facturas_de_la_cuenta_verificada(): void
    {
        $this->fakeSiic();

        $this->verificado()->getJson('/consulta-deuda/facturas')
            ->assertOk()
            ->assertJson(['facturas' => [[
                'indice' => 0, 'detalle' => 'Fact Energia AGOSTO/2026', 'importe' => '436.00', 'pagado_el' => '20/08/2026 08:20',
            ]]]);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/v1/clientes/197596/pagos') && str_contains($r->url(), 'limit=30'));
    }

    public function test_sin_cuenta_verificada_o_vencida_no_lista_nada(): void
    {
        $this->fakeSiic();

        $this->getJson('/consulta-deuda/facturas')->assertForbidden();
        $this->verificado(at: now()->subMinutes(31)->timestamp)->getJson('/consulta-deuda/facturas')->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_el_pdf_se_pide_con_la_clave_guardada_en_sesion(): void
    {
        $this->fakeSiic();
        $this->verificado()->getJson('/consulta-deuda/facturas')->assertOk();

        $res = $this->get('/consulta-deuda/facturas/0/pdf');

        $res->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $res->getContent());
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/v1/comprobantes')
            && $r['formato'] === 'pdf'
            && $r['items'][0]['nro_comprobante'] === '3154854'
            && $r['items'][0]['nro_cliente'] === '197596'
            && ! isset($r['items'][0]['detalle']));
    }

    public function test_pdf_sin_lista_en_sesion_la_vuelve_a_pedir_si_la_cuenta_sigue_verificada(): void
    {
        $this->fakeSiic();

        $this->verificado()->get('/consulta-deuda/facturas/0/pdf')
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_con_indice_inexistente_da_404_y_sin_verificar_403(): void
    {
        $this->fakeSiic();

        $this->get('/consulta-deuda/facturas/0/pdf')->assertForbidden()->assertSee('Tu consulta venció');

        $this->verificado()->getJson('/consulta-deuda/facturas')->assertOk();
        $this->get('/consulta-deuda/facturas/5/pdf')->assertNotFound();
    }

    public function test_se_descartan_los_comprobantes_que_el_siic_no_sabe_imprimir(): void
    {
        $nc = array_merge(self::ITEM, ['nro_comprobante' => '999', 'tipo' => '80', 'detalle' => 'NC. DEVOLUCION DE GARANTIA JULIO/2026', 'importe' => '-33.00']);
        Http::fake(['*/v1/clientes/197596/pagos*' => Http::response(['items' => [$nc, self::ITEM]])]);

        $this->verificado()->getJson('/consulta-deuda/facturas')
            ->assertOk()
            ->assertJsonCount(1, 'facturas')
            ->assertJsonPath('facturas.0.detalle', 'Fact Energia AGOSTO/2026');
    }

    public function test_si_se_verifica_otra_cuenta_no_se_puede_bajar_la_factura_de_la_anterior(): void
    {
        $this->fakeSiic();
        $this->verificado()->getJson('/consulta-deuda/facturas')->assertOk();

        // Consultó otra cuenta en la misma sesión: la lista vieja ya no sirve, se pide la de la
        // cuenta nueva (vacía acá) y nunca se baja la factura de la anterior.
        Http::fake(['*/v1/clientes/111/pagos*' => Http::response(['items' => []])]);
        $this->verificado('111')->get('/consulta-deuda/facturas/0/pdf')->assertNotFound();
        Http::assertNotSent(fn (Request $r) => str_ends_with($r->url(), '/v1/comprobantes'));
    }

    public function test_si_el_siic_no_genera_el_pdf_muestra_aviso_503(): void
    {
        Http::fake([
            '*/v1/clientes/197596/pagos*' => Http::response(['items' => [self::ITEM]]),
            '*/v1/comprobantes' => Http::response(['error' => 'No existe registro en CFC510'], 404),
        ]);
        $this->verificado()->getJson('/consulta-deuda/facturas')->assertOk();

        $this->get('/consulta-deuda/facturas/0/pdf')->assertStatus(503)->assertSee('No pudimos generar tu factura');
    }

    public function test_consultar_la_deuda_con_la_cuenta_correcta_habilita_las_facturas(): void
    {
        Http::fake(['*consulta/cliente*' => Http::response([
            'nro_cliente' => '197596', 'zona' => 8, 'manzano' => 223, 'correlativo' => 1345, 'deuda' => [],
        ])]);

        // Sin HandleInertiaRequests: sus props compartidas (menús) leen tablas que esta base no crea.
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ])->post('/consulta-deuda', ['nro_cliente' => '197596', 'zona' => '8', 'manzano' => '223', 'correlativo' => '1345'])
            ->assertOk()
            ->assertSessionHas(FacturasClienteController::SESION_VERIFICADO.'.nro_cliente', '197596');
    }

    public function test_consultar_con_la_cuenta_equivocada_no_habilita_nada(): void
    {
        Http::fake(['*consulta/cliente*' => Http::response([
            'nro_cliente' => '197596', 'zona' => 8, 'manzano' => 223, 'correlativo' => 1345, 'deuda' => [],
        ])]);

        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ])->post('/consulta-deuda', ['nro_cliente' => '197596', 'zona' => '9', 'manzano' => '223', 'correlativo' => '1345'])
            ->assertSessionMissing(FacturasClienteController::SESION_VERIFICADO);
    }
}
