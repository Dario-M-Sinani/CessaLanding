<?php

namespace Tests\Feature\Payments;

use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Http;

class BnbCallbackControllerTest extends PaymentsTestCase
{
    private function notificar(array $payload)
    {
        return $this->postJson('/api/pagos/bnb/receive-notification', $payload);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'QRId' => '900001',
            'Gloss' => '1 comp 197596',
            'sourceBankId' => 1,
            'originName' => 'JUAN PEREZ',
            'VoucherId' => '1J9F121212',
            'TransactionDateTime' => '19/10/2026 17:30:15',
            'additionalData' => 'CESSA-WEB-BNB-1',
        ], $over);
    }

    public function test_pago_confirmado_por_el_banco_marca_pagado_y_guarda_pagador(): void
    {
        $recibo = $this->crearReciboBnb(['alias' => 'CESSA-WEB-BNB-1', 'provider_qr_id' => '900001']);

        // El controller verifica contra el BNB (getQRStatusAsync) antes de marcar Pagado.
        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 900001, 'statusId' => 2, 'voucherId' => '1J9F121212']),
        ]));

        $this->notificar($this->payload())
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'OK']);

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Pagado, $recibo->status);
        $this->assertNotNull($recibo->paid_at);
        $this->assertSame('1J9F121212', $recibo->provider_order_number);
        $this->assertSame('JUAN PEREZ', $recibo->payer_name);
    }

    public function test_reconcilia_por_qr_id_cuando_no_viene_additional_data(): void
    {
        $recibo = $this->crearReciboBnb(['alias' => 'CESSA-WEB-BNB-1', 'provider_qr_id' => '900042']);

        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 900042, 'statusId' => 2, 'voucherId' => '0']),
        ]));

        $this->notificar($this->payload(['QRId' => '900042', 'additionalData' => null]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(PaymentStatus::Pagado, $recibo->refresh()->status);
    }

    public function test_qr_desconocido_responde_success_false_y_no_toca_nada(): void
    {
        Http::fake($this->fakeToken());

        $this->notificar($this->payload(['QRId' => '000', 'additionalData' => 'NO-EXISTE']))
            ->assertOk()
            ->assertJson(['success' => false]);

        Http::assertNotSent(fn ($r) => $r->url() === $this->urlEstado());
    }

    public function test_no_marca_pagado_si_el_banco_no_lo_reporta_pagado(): void
    {
        $recibo = $this->crearReciboBnb(['alias' => 'CESSA-WEB-BNB-1', 'provider_qr_id' => '900001']);

        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 900001, 'statusId' => 1, 'voucherId' => '0']),
        ]));

        $this->notificar($this->payload())
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertSame(PaymentStatus::Pendiente, $recibo->refresh()->status);
    }

    public function test_un_qr_vencido_pero_pagado_se_honra(): void
    {
        // Venció localmente pero el BNB lo reporta pagado: hay que marcarlo Pagado igual.
        $recibo = $this->crearReciboBnb([
            'alias' => 'CESSA-WEB-BNB-1',
            'provider_qr_id' => '900001',
            'status' => PaymentStatus::Expirado,
            'expires_at' => now()->subMinutes(2),
        ]);

        Http::fake(array_merge($this->fakeToken(), [
            $this->urlEstado() => Http::response(['success' => true, 'id' => 900001, 'statusId' => 2, 'voucherId' => '1J9F121212']),
        ]));

        $this->notificar($this->payload())->assertOk()->assertJson(['success' => true]);

        $this->assertSame(PaymentStatus::Pagado, $recibo->refresh()->status);
    }

    public function test_es_idempotente_si_ya_estaba_pagado(): void
    {
        $recibo = $this->crearReciboBnb([
            'alias' => 'CESSA-WEB-BNB-1',
            'provider_qr_id' => '900001',
            'status' => PaymentStatus::Facturado,
        ]);

        Http::fake($this->fakeToken());

        $this->notificar($this->payload())
            ->assertOk()
            ->assertJson(['success' => true]);

        // No vuelve a consultar el estado ni retrocede el recibo.
        Http::assertNotSent(fn ($r) => $r->url() === $this->urlEstado());
        $this->assertSame(PaymentStatus::Facturado, $recibo->refresh()->status);
    }
}
