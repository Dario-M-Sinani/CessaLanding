<?php

namespace Tests\Feature\Payments;

use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * Regla "un solo QR vivo por cliente": mientras el cliente tenga un QR Pendiente y vigente, al
 * volver a pedir "Pagar con QR" se le DEVUELVE ese mismo QR (para que, si cerró el modal por
 * equivocación, vea el mismo código) en vez de generar otro. Apenas vence (5 min) o se paga,
 * la próxima genera uno nuevo. El chequeo va después de verificar la cuenta contra SIIC.
 */
class PagoQrCooldownTest extends PaymentsTestCase
{
    private function generar(array $over = [])
    {
        return $this->postJson('/api/pagos/generar-qr', array_merge([
            'nro_cliente' => '197596',
            'zona' => '8',
            'manzano' => '223',
            'correlativo' => '1345',
            'cantidad_meses' => 2,
        ], $over));
    }

    /** SIIC devuelve la cuenta que coincide con los datos del request (deuda opcional). */
    private function fakeSiic(array $deuda = []): void
    {
        Http::fake([
            '*consulta/cliente*' => Http::response([
                'nro_cliente' => '197596',
                'zona' => 8,
                'manzano' => 223,
                'correlativo' => 1345,
                'nombre' => 'CLIENTE PRUEBA',
                'nro_cuenta' => '822301345',
                'deuda' => $deuda,
            ]),
        ]);
    }

    public function test_un_qr_vigente_devuelve_el_mismo_qr_sin_generar_otro(): void
    {
        $activo = $this->crearReciboBnb([
            'nro_cliente' => '197596',
            'provider' => 'sip_bisa',
            'alias' => 'CESSA-WEB-EXISTENTE',
            'status' => PaymentStatus::Pendiente,
            'expires_at' => now()->addMinutes(4),
            'qr_image_path' => 'recibos/qr/CESSA-WEB-EXISTENTE.png',
            'amount' => 150.50,
            'debt_items' => [
                ['nro_cliente' => '197596', 'anio' => 2025, 'mes' => 10, 'importe' => 100],
                ['nro_cliente' => '197596', 'anio' => 2025, 'mes' => 11, 'importe' => 50.5],
            ],
        ]);

        $this->fakeSiic();

        $this->generar()
            ->assertOk()
            ->assertJson([
                'alias' => 'CESSA-WEB-EXISTENTE',
                'monto' => '150.50',
                'periodo' => 'Octubre-Noviembre/2025',
            ]);

        // No se creó ningún recibo nuevo ni se llamó a ningún banco a generar.
        $this->assertSame(1, Recibo::count());
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'getQRWithImageAsync') || str_contains($r->url(), 'generaQr'));
    }

    public function test_un_qr_ya_vencido_no_se_devuelve_sigue_el_flujo_normal(): void
    {
        // Pendiente pero vencido: no cuenta como activo. Con deuda vacía en SIIC, el flujo sigue
        // de largo y corta con 422 (no devuelve el QR viejo).
        $this->crearReciboBnb([
            'nro_cliente' => '197596',
            'alias' => 'CESSA-WEB-VIEJO',
            'status' => PaymentStatus::Pendiente,
            'expires_at' => now()->subMinutes(1),
            'qr_image_path' => 'recibos/qr/CESSA-WEB-VIEJO.png',
        ]);

        $this->fakeSiic([]); // sin deuda pendiente

        $res = $this->generar();

        $res->assertStatus(422);
        $this->assertNotSame('CESSA-WEB-VIEJO', $res->json('alias'));
    }

    public function test_un_qr_ya_pagado_no_se_devuelve(): void
    {
        $this->crearReciboBnb([
            'nro_cliente' => '197596',
            'alias' => 'CESSA-WEB-PAGADO',
            'status' => PaymentStatus::Pagado,
            'expires_at' => now()->addMinutes(4),
            'qr_image_path' => 'recibos/qr/CESSA-WEB-PAGADO.png',
        ]);

        $this->fakeSiic([]);

        $res = $this->generar();

        $res->assertStatus(422);
        $this->assertNotSame('CESSA-WEB-PAGADO', $res->json('alias'));
    }

    public function test_cuenta_que_no_coincide_no_expone_el_qr_activo(): void
    {
        $this->crearReciboBnb([
            'nro_cliente' => '197596',
            'alias' => 'CESSA-WEB-EXISTENTE',
            'status' => PaymentStatus::Pendiente,
            'expires_at' => now()->addMinutes(4),
            'qr_image_path' => 'recibos/qr/CESSA-WEB-EXISTENTE.png',
        ]);

        $this->fakeSiic();

        // N° de cuenta equivocado -> 422 antes de mirar el QR activo.
        $this->generar(['manzano' => '999'])
            ->assertStatus(422)
            ->assertJsonMissing(['alias' => 'CESSA-WEB-EXISTENTE']);
    }
}
