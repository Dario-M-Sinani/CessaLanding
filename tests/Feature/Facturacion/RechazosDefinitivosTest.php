<?php

namespace Tests\Feature\Facturacion;

use App\Services\Cobranzas\FacturacionRecibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Http;

/**
 * Rechazos del gateway que no se arreglan reintentando (ver FacturacionRecibo::RECHAZOS_DEFINITIVOS):
 * el Recibo queda para revisión manual y el modal del cliente deja de prometer la factura.
 */
class RechazosDefinitivosTest extends FacturacionTestCase
{
    private function rechazar(string $error, array $atributos = [])
    {
        Http::fake([
            self::GATEWAY.'/api/externo/recibos-web/liquidar/' => Http::response(['estado' => 'ERROR', 'error' => $error]),
        ]);
        $recibo = $this->crearRecibo($atributos);
        app(FacturacionRecibo::class)->procesar($recibo);

        return $recibo->refresh();
    }

    public function test_deuda_cambiada_no_se_reintenta_sola(): void
    {
        $recibo = $this->rechazar(
            'La deuda cambió desde que se generó el QR: ya no están pendientes N° 20253 (NC. CONCILIACIÓN JULIO/2026).'
        );

        $this->assertSame(PaymentStatus::ErrorFacturacion, $recibo->status);
        $this->assertGreaterThanOrEqual(FacturacionRecibo::MAX_INTENTOS_AUTOMATICOS, $recibo->facturacion_intentos);
    }

    public function test_monto_distinto_no_se_reintenta_solo(): void
    {
        $recibo = $this->rechazar('El monto cobrado (Bs. 200.00) no coincide con la suma de los comprobantes (Bs. 150.50); no se pagó nada.');

        $this->assertGreaterThanOrEqual(FacturacionRecibo::MAX_INTENTOS_AUTOMATICOS, $recibo->facturacion_intentos);
    }

    public function test_un_rechazo_comun_si_se_reintenta(): void
    {
        $recibo = $this->rechazar('CFC510: cliente sin datos de facturación', ['facturacion_intentos' => 1]);

        $this->assertSame(2, $recibo->facturacion_intentos);
    }

    public function test_es_rechazo_definitivo(): void
    {
        $this->assertTrue(FacturacionRecibo::esRechazoDefinitivo('La deuda cambió desde que se generó el QR: x'));
        $this->assertTrue(FacturacionRecibo::esRechazoDefinitivo('pagar transacción: La deuda no existe con los datos'));
        $this->assertFalse(FacturacionRecibo::esRechazoDefinitivo('aperturar caja: fuera de horario'));
        $this->assertFalse(FacturacionRecibo::esRechazoDefinitivo(null));
    }

    public function test_estado_del_qr_avisa_revision_manual_sin_exponer_el_motivo(): void
    {
        $recibo = $this->rechazar('La deuda cambió desde que se generó el QR: ya no están pendientes N° 1.');

        $this->getJson("/api/pagos/estado-qr/{$recibo->alias}")
            ->assertOk()
            ->assertJson(['status' => 'error_facturacion', 'revision_manual' => true])
            ->assertJsonMissingPath('facturacion_error');
    }

    public function test_estado_del_qr_sin_revision_manual_en_un_error_reintentable(): void
    {
        $recibo = $this->rechazar('CFC510: cliente sin datos de facturación');

        $this->getJson("/api/pagos/estado-qr/{$recibo->alias}")
            ->assertOk()
            ->assertJson(['status' => 'error_facturacion', 'revision_manual' => false]);
    }
}
