<?php

namespace Tests\Feature\Payments;

use App\Models\User;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * Botón "Simular pago" del modal del QR (solo pruebas): exige PAGOS_SIMULACION_HABILITADA y
 * estar logueado con rol SYSTEM; si no, la ruta responde 404 como si no existiera. Antes de
 * marcar Pagado inhabilita el QR en el banco, para que no quede pagable de verdad.
 */
class SimularPagoTest extends PaymentsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // La base de pagos no trae la columna `role` de users; acá hace falta para el permiso.
        $this->artisan('migrate', ['--path' => ['database/migrations/2026_07_29_120000_add_role_to_users_table.php']]);
    }

    private function usuario(string $rol): User
    {
        return User::forceCreate([
            'name' => "Usuario {$rol}",
            'email' => strtolower($rol).'@cessa.test',
            'password' => 'secreto-de-prueba',
            'role' => $rol,
        ]);
    }

    private function simular(string $alias)
    {
        return $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->postJson("/api/pagos/simular-pago/{$alias}");
    }

    public function test_system_con_simulacion_habilitada_marca_pagado_e_inhabilita_en_el_banco(): void
    {
        config(['services.pagos.simulacion_habilitada' => true]);
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '77701']);
        Http::fake($this->fakeToken() + [
            $this->urlCancelar() => Http::response(['success' => true, 'message' => null]),
        ]);

        $this->actingAs($this->usuario(User::ROLE_SYSTEM));
        $this->simular($recibo->alias)->assertOk()->assertJson(['status' => 'pagado']);

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Pagado, $recibo->status);
        $this->assertNotNull($recibo->paid_at);
        $this->assertTrue($recibo->callback_payload['simulado']);
        Http::assertSent(fn (Request $r) => $r->url() === $this->urlCancelar() && (string) $r['qrId'] === '77701');
    }

    public function test_sin_la_variable_habilitada_responde_404_aunque_sea_system(): void
    {
        config(['services.pagos.simulacion_habilitada' => false]);
        $recibo = $this->crearReciboBnb();

        $this->actingAs($this->usuario(User::ROLE_SYSTEM));
        $this->simular($recibo->alias)->assertNotFound();

        $this->assertSame(PaymentStatus::Pendiente, $recibo->fresh()->status);
    }

    public function test_sin_login_o_con_otro_rol_responde_404(): void
    {
        config(['services.pagos.simulacion_habilitada' => true]);
        $recibo = $this->crearReciboBnb();

        $this->simular($recibo->alias)->assertNotFound();

        $this->actingAs($this->usuario(User::ROLE_ADMIN));
        $this->simular($recibo->alias)->assertNotFound();

        $this->assertSame(PaymentStatus::Pendiente, $recibo->fresh()->status);
    }

    public function test_si_el_banco_no_inhabilita_el_qr_no_se_simula(): void
    {
        config(['services.pagos.simulacion_habilitada' => true]);
        $recibo = $this->crearReciboBnb();
        Http::fake($this->fakeToken() + [
            $this->urlCancelar() => Http::response(['success' => false, 'message' => 'error'], 500),
        ]);

        $this->actingAs($this->usuario(User::ROLE_SYSTEM));
        $this->simular($recibo->alias)->assertStatus(502);

        $this->assertSame(PaymentStatus::Pendiente, $recibo->fresh()->status);
    }

    public function test_un_qr_que_no_esta_pendiente_no_se_simula(): void
    {
        config(['services.pagos.simulacion_habilitada' => true]);
        $recibo = $this->crearReciboBnb(['status' => PaymentStatus::Expirado]);

        $this->actingAs($this->usuario(User::ROLE_SYSTEM));
        $this->simular($recibo->alias)->assertStatus(422);

        $this->assertSame(PaymentStatus::Expirado, $recibo->fresh()->status);
    }

    private function generarSimulado()
    {
        return $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->postJson('/api/pagos/generar-qr', [
                'nro_cliente' => '197596', 'zona' => '8', 'manzano' => '223', 'correlativo' => '1345',
                'cantidad_meses' => 1, 'simular' => true,
            ]);
    }

    public function test_simular_desde_consulta_crea_el_recibo_pagado_sin_tocar_ningun_banco(): void
    {
        config(['services.pagos.simulacion_habilitada' => true]);
        Http::fake([
            '*consulta/cliente*' => Http::response([
                'nro_cliente' => '197596', 'zona' => 8, 'manzano' => 223, 'correlativo' => 1345,
                'deuda' => [
                    ['nro_cliente' => '197596', 'anio' => 2026, 'mes' => 1, 'importe' => 22.30, 'debito_credito' => 'DEBITO'],
                    ['nro_cliente' => '197596', 'anio' => 2026, 'mes' => 2, 'importe' => 30, 'debito_credito' => 'DEBITO'],
                ],
            ]),
        ]);

        $this->actingAs($this->usuario(User::ROLE_SYSTEM));
        $res = $this->generarSimulado()->assertOk()->assertJson(['status' => 'pagado', 'monto' => '22.30', 'qr_image_url' => null]);

        $recibo = \App\Models\Recibo::where('alias', $res->json('alias'))->firstOrFail();
        $this->assertStringStartsWith('CESSA-SIM-', $recibo->alias);
        $this->assertSame(PaymentStatus::Pagado, $recibo->status);
        $this->assertCount(1, $recibo->debt_items);
        Http::assertSentCount(1); // solo la consulta a SIIC, ningún banco
    }

    public function test_simular_desde_consulta_sin_permiso_responde_404(): void
    {
        config(['services.pagos.simulacion_habilitada' => true]);
        Http::fake(['*consulta/cliente*' => Http::response([
            'nro_cliente' => '197596', 'zona' => 8, 'manzano' => 223, 'correlativo' => 1345, 'deuda' => [],
        ])]);

        $this->actingAs($this->usuario(User::ROLE_ADMIN));
        $this->generarSimulado()->assertNotFound();
        $this->assertSame(0, \App\Models\Recibo::count());
    }
}
