<?php

namespace App\Services\Cobranzas;

use App\Models\Recibo;
use App\Services\Cobranzas\Exceptions\CobranzasException;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Registra un Recibo ya Pagado como factura real -- vía el gateway propio (cobranza-cessa,
 * ver CobranzasGatewayClient), porque este sitio (Hostinger) no tiene ruta directa a
 * api-cobranzas-bancos. Usado tanto por el comando programado (RegistrarFacturacionCobranzas)
 * como por la acción manual "Reintentar Facturación" del panel (ver ReciboResource).
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
            $resultado = $this->gateway->liquidar(
                alias: $recibo->alias,
                nroCliente: $recibo->nro_cliente,
                monto: (float) $recibo->amount,
                moneda: strtoupper((string) $recibo->currency) === 'USD' ? 'USD' : 'BOB',
                detalle: $recibo->debt_items,
                fechaPago: ($recibo->paid_at ?? now())->toIso8601String(),
                numeroOrdenOriginante: $recibo->provider_order_number ?: '',
            );

            if (! empty($resultado['cobranzas_uuid']) && ! $recibo->cobranzas_uuid) {
                $recibo->update(['cobranzas_uuid' => $resultado['cobranzas_uuid']]);
            }

            if (($resultado['estado'] ?? null) !== 'FACTURADO') {
                $this->marcarError($recibo, $resultado['error'] ?: 'El gateway no pudo facturar el recibo (sin detalle de error).');

                return;
            }

            $pdf = $this->gateway->comprobantePdf($recibo->alias);
            $path = "recibos/comprobantes/{$recibo->alias}.pdf";
            Storage::disk('public')->put($path, $pdf);

            $recibo->update([
                'status' => PaymentStatus::Facturado,
                'comprobante_path' => $path,
                'facturado_at' => now(),
                'facturacion_error' => null,
            ]);

            Log::info('cobranzas.facturado', [
                'recibo_id' => $recibo->id,
                'alias' => $recibo->alias,
                'cobranzas_uuid' => $resultado['cobranzas_uuid'] ?? $recibo->cobranzas_uuid,
            ]);
        } catch (CobranzasException $e) {
            report($e);
            $this->marcarError($recibo, $e->getMessage());
        } catch (\Throwable $e) {
            // El dinero ya se cobró -- una excepción inesperada acá (red caída hacia el
            // gateway, config mal puesta, un cambio de contrato que no contemplamos, etc.)
            // nunca debe tumbar el comando/la acción sin dejar rastro.
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
