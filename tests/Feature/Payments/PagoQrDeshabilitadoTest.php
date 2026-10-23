<?php

namespace Tests\Feature\Payments;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Sitio con el pago por QR apagado (PAGOS_QR_HABILITADO=false, como cessa.com.bo mientras el QR
 * se prueba en test.cessa.com.bo): nada del circuito de pago responde aunque se le pegue a
 * mano, el scheduler no corre las tareas de pagos y el panel no ofrece "Cobros QR".
 */
class PagoQrDeshabilitadoTest extends PaymentsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.pagos.qr_habilitado' => false]);
    }

    public function test_generar_qr_responde_404_sin_llamar_al_siic(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->postJson('/api/pagos/generar-qr', [
                'nro_cliente' => '12345',
                'zona' => '5',
                'manzano' => '59',
                'correlativo' => '7421',
            ])
            ->assertNotFound();
        // Http::preventStrayRequests() (PaymentsTestCase) haría fallar el test si se hubiera
        // consultado al SIIC o a un banco.
    }

    public function test_estado_qr_responde_404_aunque_el_recibo_exista(): void
    {
        $recibo = $this->crearReciboBnb();

        $this->getJson("/api/pagos/estado-qr/{$recibo->alias}")->assertNotFound();
    }

    public function test_simular_pago_responde_404_aunque_la_simulacion_este_prendida(): void
    {
        $this->artisan('migrate', ['--path' => ['database/migrations/2026_07_29_120000_add_role_to_users_table.php']]);
        config(['services.pagos.simulacion_habilitada' => true]);
        $recibo = $this->crearReciboBnb();
        $this->actingAs(User::forceCreate([
            'name' => 'Usuario SYSTEM',
            'email' => 'system@cessa.test',
            'password' => 'secreto-de-prueba',
            'role' => User::ROLE_SYSTEM,
        ]));

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->postJson("/api/pagos/simular-pago/{$recibo->alias}")
            ->assertNotFound();
    }

    public function test_el_scheduler_no_corre_las_tareas_de_pagos(): void
    {
        $pagos = collect(app(Schedule::class)->events())
            ->filter(fn ($evento) => str_contains((string) $evento->command, 'pagos:'));

        $this->assertCount(4, $pagos);
        foreach ($pagos as $evento) {
            $this->assertFalse($evento->filtersPass($this->app), $evento->command);
        }

        config(['services.pagos.qr_habilitado' => true]);
        foreach ($pagos as $evento) {
            $this->assertTrue($evento->filtersPass($this->app), $evento->command);
        }
    }

    public function test_cobros_qr_no_aparece_en_el_panel(): void
    {
        $this->assertFalse(\App\Filament\Resources\ReciboResource::canAccess());
    }
}
