<?php

namespace App\Services\Cobranzas;

use App\Models\Recibo;
use App\Services\Cobranzas\Exceptions\CobranzasException;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Cache;
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

    // Rechazos del SIIC que no se arreglan reintentando: el cron no los vuelve a intentar solo
    // (cada intento deja otra Transacción FALLIDA en api-cobranzas) y quedan para revisión manual
    // desde el panel ("Reintentar Facturación"). "Ya figura pagado por otro medio" en producción
    // significa que el cliente pagó esos meses por otro canal mientras pagaba el QR: el dinero
    // entró dos veces y hay que regularizarlo (devolución o saldo a favor).
    //
    // "La deuda cambió desde que se generó el QR" y "no coincide con la suma de los comprobantes"
    // son las verificaciones que hace el gateway ANTES de pagar (cobranza-cessa, 2026-10-08): entre
    // que se generó el QR y el pago algún comprobante se pagó por otro medio, cambió de importe o
    // apareció uno más antiguo; o el monto del recibo no es la suma de sus comprobantes. Reintentar
    // da siempre lo mismo -- el cliente ya pagó y hay que aplicarlo a mano.
    private const RECHAZOS_DEFINITIVOS = [
        'ya figura pagado por otro medio',
        'La deuda no existe',
        'La deuda cambió desde que se generó el QR',
        'no coincide con la suma de los comprobantes',
    ];

    /**
     * El error de facturación no se arregla reintentando: el Recibo queda para revisión manual
     * (el cron no lo vuelve a intentar y al cliente no se le promete una factura automática).
     */
    public static function esRechazoDefinitivo(?string $motivo): bool
    {
        return $motivo !== null
            && collect(self::RECHAZOS_DEFINITIVOS)->contains(fn (string $t) => str_contains($motivo, $t));
    }

    // Rechazos por la caja del SIIC: se arreglan solos cuando la caja vuelve a estar en horario
    // (operador de cobranza con horario en OPERCOB, ej. "De 07:50:00 a 18:50:00") o con la caja
    // del día siguiente (la del día se cierra a mano). No gastan intentos: el cron lo sigue
    // intentando cada minuto hasta que pase. Falla en "aperturar caja", antes de crear la
    // Transacción, así que no deja transacciones FALLIDA en api-cobranzas.
    private const RECHAZOS_DE_CAJA = [
        'fuera de horario',
        'la caja del día de hoy ha sido cerrada',
    ];

    public function __construct(private readonly CobranzasGatewayClient $gateway)
    {
    }

    /**
     * Factura el Recibo apenas termina la request que confirmó el pago (callback del banco o pago
     * simulado), después de mandar la respuesta: así el cliente ve su factura en segundos en vez
     * de esperar a la próxima vuelta del cron (hasta 1 min), y el banco no espera a la facturación.
     * El cron (pagos:registrar-facturacion) sigue igual como respaldo y para los reintentos.
     */
    public static function facturarTrasRespuesta(Recibo $recibo): void
    {
        if (! config('services.cobranzas.enabled')) {
            return;
        }

        $id = $recibo->id;

        app()->terminating(function () use ($id): void {
            if ($recibo = Recibo::find($id)) {
                app(self::class)->procesar($recibo);
            }
        });
    }

    public function procesar(Recibo $recibo): void
    {
        // Ahora puede llegar por dos lados a la vez (facturarTrasRespuesta y el cron): el lock
        // por recibo evita facturarlo dos veces en paralelo, y se relee el estado ya adentro por
        // si el otro lo terminó mientras tanto.
        $lock = Cache::lock("facturar-recibo-{$recibo->id}", 120);

        if (! $lock->get()) {
            return;
        }

        try {
            $recibo->refresh();

            if (! in_array($recibo->status, [PaymentStatus::Pagado, PaymentStatus::ErrorFacturacion], true)) {
                return;
            }

            $this->facturar($recibo);
        } finally {
            $lock->release();
        }
    }

    private function facturar(Recibo $recibo): void
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
                banco: (string) $recibo->provider,
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
        $contiene = fn (array $textos) => collect($textos)->contains(fn (string $t) => str_contains($motivo, $t));

        $intentos = match (true) {
            self::esRechazoDefinitivo($motivo) => max($recibo->facturacion_intentos + 1, self::MAX_INTENTOS_AUTOMATICOS),
            $contiene(self::RECHAZOS_DE_CAJA) => $recibo->facturacion_intentos,
            default => $recibo->facturacion_intentos + 1,
        };

        $recibo->update([
            'status' => PaymentStatus::ErrorFacturacion,
            'facturacion_intentos' => $intentos,
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
