<?php

namespace Tests\Feature\Payments;

use App\Services\Payments\PaymentStatus;

/**
 * Callback de SIP (Banco BISA, producción real): confirma que un QR fue pagado. Es el eslabón
 * más usado del cobro real, así que se cubren el camino feliz, la seguridad (Basic Auth), el
 * alias desconocido y la idempotencia ante reintentos de SIP.
 */
class SipCallbackControllerTest extends PaymentsTestCase
{
    private const AUTH = ['Authorization' => 'Basic '.self::CREDS];

    private const CREDS = 'c2lwLXVzZXI6c2lwLXBhc3M='; // base64("sip-user:sip-pass")

    private function payload(string $alias, array $over = []): array
    {
        return array_merge([
            'alias' => $alias,
            'numeroOrdenOriginante' => 'ORD-999',
            'monto' => '150.50',
            'idQr' => 'QR-1',
            'moneda' => 'BOB',
            'cuentaCliente' => '1234567',
            'nombreCliente' => 'JUAN PEREZ',
            'documentoCliente' => '9876543',
        ], $over);
    }

    private function llamar(array $payload, array $headers = self::AUTH)
    {
        return $this->postJson('/api/pagos/sip/confirmar-pago', $payload, $headers);
    }

    public function test_pago_confirmado_marca_pagado_y_guarda_datos_del_pagador(): void
    {
        $recibo = $this->crearReciboBnb(['provider' => 'sip_bisa', 'alias' => 'CESSA-WEB-SIP-1', 'status' => PaymentStatus::Pendiente]);

        $this->llamar($this->payload('CESSA-WEB-SIP-1'))
            ->assertOk()
            ->assertJson(['codigo' => '0000']);

        $recibo->refresh();
        $this->assertSame(PaymentStatus::Pagado, $recibo->status);
        $this->assertNotNull($recibo->paid_at);
        $this->assertSame('ORD-999', $recibo->provider_order_number);
        $this->assertSame('JUAN PEREZ', $recibo->payer_name);
        $this->assertSame('9876543', $recibo->payer_document);
        $this->assertSame('1234567', $recibo->payer_account);
    }

    public function test_sin_basic_auth_devuelve_401_y_no_toca_el_recibo(): void
    {
        $recibo = $this->crearReciboBnb(['provider' => 'sip_bisa', 'alias' => 'CESSA-WEB-SIP-2', 'status' => PaymentStatus::Pendiente]);

        $this->postJson('/api/pagos/sip/confirmar-pago', $this->payload('CESSA-WEB-SIP-2'))
            ->assertStatus(401);

        $this->assertSame(PaymentStatus::Pendiente, $recibo->fresh()->status);
    }

    public function test_basic_auth_incorrecto_devuelve_401(): void
    {
        $this->crearReciboBnb(['provider' => 'sip_bisa', 'alias' => 'CESSA-WEB-SIP-3', 'status' => PaymentStatus::Pendiente]);

        $this->llamar($this->payload('CESSA-WEB-SIP-3'), ['Authorization' => 'Basic '.base64_encode('sip-user:MALA')])
            ->assertStatus(401);
    }

    public function test_alias_desconocido_responde_9999_sin_error(): void
    {
        $this->llamar($this->payload('NO-EXISTE'))
            ->assertOk()
            ->assertJson(['codigo' => '9999']);
    }

    public function test_es_idempotente_no_refactura_un_recibo_ya_facturado(): void
    {
        // Un reintento de SIP sobre un Recibo YA facturado no debe retrocederlo a Pagado (eso
        // haría que el cron lo facture de nuevo -> doble facturación).
        $recibo = $this->crearReciboBnb([
            'provider' => 'sip_bisa',
            'alias' => 'CESSA-WEB-SIP-4',
            'status' => PaymentStatus::Facturado,
            'paid_at' => now()->subHour(),
        ]);

        $this->llamar($this->payload('CESSA-WEB-SIP-4'))
            ->assertOk()
            ->assertJson(['codigo' => '0000']);

        $this->assertSame(PaymentStatus::Facturado, $recibo->fresh()->status);
    }

    public function test_reintento_sobre_un_ya_pagado_no_lo_reprocesa(): void
    {
        $recibo = $this->crearReciboBnb([
            'provider' => 'sip_bisa',
            'alias' => 'CESSA-WEB-SIP-5',
            'status' => PaymentStatus::Pagado,
            'paid_at' => now()->subMinutes(10),
            'provider_order_number' => 'ORD-ORIGINAL',
        ]);

        $this->llamar($this->payload('CESSA-WEB-SIP-5', ['numeroOrdenOriginante' => 'ORD-NUEVO']))
            ->assertOk();

        // No se pisa el nº de orden original ni se cambia paid_at.
        $this->assertSame('ORD-ORIGINAL', $recibo->fresh()->provider_order_number);
    }

    public function test_un_qr_vencido_pero_pagado_se_honra(): void
    {
        // El QR venció localmente (5 min) pero SIP confirma un pago real: hay que marcarlo Pagado
        // igual, el dinero entró.
        $recibo = $this->crearReciboBnb([
            'provider' => 'sip_bisa',
            'alias' => 'CESSA-WEB-SIP-6',
            'status' => PaymentStatus::Expirado,
            'expires_at' => now()->subMinutes(2),
        ]);

        $this->llamar($this->payload('CESSA-WEB-SIP-6'))->assertOk();

        $this->assertSame(PaymentStatus::Pagado, $recibo->fresh()->status);
    }
}
