<?php

namespace App\Services\Cobranzas;

use App\Models\Recibo;
use App\Services\Cobranzas\Exceptions\CobranzasException;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Registra un Recibo ya Pagado como factura real en api-cobranzas-bancos y guarda el
 * comprobante en PDF -- usado tanto por el comando programado (RegistrarFacturacionCobranzas)
 * como por la acción manual "Reintentar Facturación" del panel (ver ReciboResource).
 *
 * cessa-laravel (Hostinger) no tiene ruta hacia la red interna de CESSA, así que no arma ni manda
 * el pago directo a api-cobranzas-bancos -- le avisa al gateway de `cobranza_cessa`
 * (CobranzasGatewayClient, corre en 10.1.1.88, dentro de esa red) con el detalle crudo de deuda,
 * y es ese backend el que arma el "detalle"/"documento" reales y paga (ver
 * apps/facturacion_externa de ese repo).
 *
 * El dinero ya se cobró antes de llegar acá (status Pagado) -- este paso nunca debe hacer que
 * ese hecho se pierda de vista: si falla, el Recibo queda en ErrorFacturacion con el motivo
 * guardado, nunca vuelve a Pendiente/silencioso.
 */
class FacturacionRecibo
{
    // Tope de reintentos automáticos del comando programado antes de dejar de insistir solo y
    // requerir que alguien revise a mano desde el panel (con el botón "Reintentar Facturación",
    // que sí se puede usar las veces que hagan falta).
    public const MAX_INTENTOS_AUTOMATICOS = 5;

    public function __construct(private readonly CobranzasGatewayClient $gateway)
    {
    }

    public function procesar(Recibo $recibo): void
    {
        if (empty($recibo->debt_items)) {
            $this->marcarError($recibo, 'El recibo no tiene guardado el detalle de deuda (debt_items) -- no se puede facturar. Revisar manualmente contra SIIC.');

            return;
        }

        try {
            $respuesta = $this->gateway->liquidar([
                'alias' => $recibo->alias,
                'nro_cliente' => $recibo->nro_cliente,
                'monto' => (float) $recibo->amount,
                'moneda' => $recibo->currency ?: 'BOB',
                'detalle' => $recibo->debt_items,
                'fecha_pago' => ($recibo->paid_at ?? now())->toIso8601String(),
                'numero_orden_originante' => $recibo->provider_order_number ?: '',
            ]);

            if (($respuesta['estado'] ?? null) !== 'facturado') {
                $this->marcarError($recibo, $respuesta['error'] ?: 'El gateway de facturación no confirmó el pago (respuesta sin estado "facturado").');

                return;
            }

            $pdf = $this->gateway->obtenerComprobantePdf($recibo->alias);
            $path = "recibos/comprobantes/{$recibo->alias}.pdf";
            Storage::disk('public')->put($path, $pdf);

            $recibo->update([
                'status' => PaymentStatus::Facturado,
                'cobranzas_uuid' => $respuesta['cobranzas_uuid'] ?: $recibo->cobranzas_uuid,
                'comprobante_path' => $path,
                'facturado_at' => now(),
                'facturacion_error' => null,
            ]);

            Log::info('cobranzas.facturado', [
                'recibo_id' => $recibo->id,
                'alias' => $recibo->alias,
                'cobranzas_uuid' => $respuesta['cobranzas_uuid'] ?? null,
            ]);
        } catch (CobranzasException $e) {
            report($e);
            $this->marcarError($recibo, $e->getMessage());
        } catch (\Throwable $e) {
            // El dinero ya se cobró -- una excepción inesperada acá (red caída, config mal
            // puesta, un cambio de contrato del lado del gateway que no contemplamos, etc.)
            // nunca debe tumbar el comando/la acción sin dejar rastro. Caso real encontrado
            // probando la integración vieja: un ConnectionException genérico de Guzzle (URL
            // base mal armada) no es un CobranzasException y se colaba sin capturar.
            report($e);
            $this->marcarError($recibo, 'Error inesperado: '.$e->getMessage());
        }
    }

    private function marcarError(Recibo $recibo, string $motivo): void
    {
        $recibo->update([
            'status' => PaymentStatus::ErrorFacturacion,
            'facturacion_intentos' => $recibo->facturacion_intentos + 1,
            'facturacion_error' => $motivo,
        ]);

        Log::warning('cobranzas.error_facturacion', [
            'recibo_id' => $recibo->id,
            'alias' => $recibo->alias,
            'intentos' => $recibo->facturacion_intentos,
            'motivo' => $motivo,
        ]);
    }
}
