<?php

namespace Tests\Feature\Payments;

use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Http;

/**
 * La web no recibe el aviso de pago del BNB (la URL de notificación de la cuenta compartida
 * apunta a otro sistema), así que los pagos BNB se confirman consultando getQRStatusAsync.
 */
class SincronizarPagosBnbTest extends PaymentsTestCase
{
    private function estadoBnb(int $statusId, string $voucher = '0'): void
    {
        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 900001, 'statusId' => $statusId, 'voucherId' => $voucher]),
        ]));
    }

    public function test_pendiente_que_el_bnb_da_por_pagado_queda_pagado(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '900001']);
        $this->estadoBnb(2, '1J9F121212');

        $this->artisan('pagos:sincronizar-bnb')->assertSuccessful();

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Pagado, $recibo->status);
        $this->assertNotNull($recibo->paid_at);
        $this->assertSame('1J9F121212', $recibo->provider_order_number);
    }

    public function test_pendiente_no_usado_sigue_pendiente(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '900001']);
        $this->estadoBnb(1);

        $this->artisan('pagos:sincronizar-bnb')->assertSuccessful();

        $this->assertSame(PaymentStatus::Pendiente, $recibo->refresh()->status);
    }

    public function test_expirado_hace_poco_pero_pagado_al_filo_se_honra(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '900001', 'status' => PaymentStatus::Expirado]);
        $this->estadoBnb(2);

        $this->artisan('pagos:sincronizar-bnb')->assertSuccessful();

        $this->assertSame(PaymentStatus::Pagado, $recibo->refresh()->status);
    }

    public function test_no_consulta_recibos_viejos_ya_facturados_ni_de_sip(): void
    {
        $viejo = $this->crearReciboBnb(['provider_qr_id' => '900001', 'status' => PaymentStatus::Expirado]);
        $viejo->forceFill(['created_at' => now()->subHours(2)])->save();
        $this->crearReciboBnb(['provider_qr_id' => '900002', 'status' => PaymentStatus::Facturado]);
        $this->crearReciboBnb(['provider' => 'sip_bisa', 'provider_qr_id' => '900003']);
        $this->estadoBnb(2);

        $this->artisan('pagos:sincronizar-bnb')->assertSuccessful();

        Http::assertNotSent(fn ($r) => $r->url() === $this->urlEstado());
        $this->assertSame(PaymentStatus::Expirado, $viejo->refresh()->status);
    }

    public function test_error_del_bnb_no_corta_el_lote(): void
    {
        $recibo = $this->crearReciboBnb(['provider_qr_id' => '900001']);
        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => false, 'message' => 'caído'], 500),
        ]));

        $this->artisan('pagos:sincronizar-bnb')->assertSuccessful();

        $this->assertSame(PaymentStatus::Pendiente, $recibo->refresh()->status);
    }
}
