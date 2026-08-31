<?php

namespace App\Services\Cobranzas;

use App\Models\Recibo;
use App\Services\Cobranzas\Exceptions\CobranzasException;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Registra un Recibo ya Pagado como factura real en api-cobranzas-bancos y guarda el
 * comprobante en PDF -- usado tanto por el comando programado (RegistrarFacturacionCobranzas)
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

    public function __construct(private readonly CobranzasBancoService $cobranzas)
    {
    }

    public function procesar(Recibo $recibo): void
    {
        if (empty($recibo->debt_items)) {
            $this->marcarError($recibo, 'El recibo no tiene guardado el detalle de deuda (debt_items) -- no se puede facturar. Revisar manualmente contra SIIC.');

            return;
        }

        if (! config('services.cobranzas.documento_ente_id') || ! config('services.cobranzas.documento_banco_id')) {
            $this->marcarError($recibo, 'Falta configurar services.cobranzas.documento_ente_id/documento_banco_id (catálogos GET /v1/entes y GET /v1/bancos de api-cobranzas-bancos) -- sin esto no se puede armar el "documento" que exige /pagar-otro-documento.');

            return;
        }

        try {
            $this->cobranzas->asegurarCajaAbierta();

            $uuid = $recibo->cobranzas_uuid ?? $this->cobranzas->crearTransaccion();
            if (! $recibo->cobranzas_uuid) {
                $recibo->update(['cobranzas_uuid' => $uuid]);
            }

            $this->cobranzas->pagarTransaccion($uuid, $this->construirDetalle($recibo), $this->construirDocumento($recibo));

            $pdf = $this->cobranzas->obtenerComprobantePdf($uuid);
            $path = "recibos/comprobantes/{$recibo->alias}.pdf";
            Storage::disk('public')->put($path, $pdf);

            $recibo->update([
                'status' => PaymentStatus::Facturado,
                'comprobante_path' => $path,
                'facturado_at' => now(),
                'facturacion_error' => null,
            ]);

            Log::info('cobranzas.facturado', ['recibo_id' => $recibo->id, 'alias' => $recibo->alias, 'cobranzas_uuid' => $uuid]);
        } catch (CobranzasException $e) {
            report($e);
            $this->marcarError($recibo, $e->getMessage());
        } catch (\Throwable $e) {
            // El dinero ya se cobró -- una excepción inesperada acá (red caída, config mal
            // puesta, un cambio de contrato del lado de api-cobranzas-bancos que no
            // contemplamos, etc.) nunca debe tumbar el comando/la acción sin dejar rastro.
            // Caso real encontrado probando esto: un ConnectionException genérico de Guzzle
            // (URL base mal armada) no es un CobranzasException y se colaba sin capturar.
            report($e);
            $this->marcarError($recibo, 'Error inesperado: '.$e->getMessage());
        }
    }

    /**
     * Arma el `detalle` (lista de "Deuda") exactamente como lo hace la app real "Cobranza"
     * (`Deuda(PendienteDto)`, decompilado con javap de `el jar/`) a partir del snapshot crudo
     * de SIIC guardado en `Recibo::debt_items`. Las claves ya vienen en snake_case desde SIIC
     * (confirmado por los mismos `@JsonProperty` del cliente real) -- lo único que hay que
     * transformar es: fechas a "yyyyMMdd" (con "00000000" si vienen vacías, mismo fallback que
     * el cliente real) e `importe` a magnitud sin signo con 2 decimales fijos (el signo lo
     * lleva `debito_credito`, nunca se manda un importe negativo).
     *
     * @return array<int, array<string, mixed>>
     */
    private function construirDetalle(Recibo $recibo): array
    {
        return collect($recibo->debt_items)->map(function (array $item) {
            return [
                'codigo_sucursal' => $item['codigo_sucursal'] ?? null,
                'nro_comprobante' => $item['nro_comprobante'] ?? null,
                'nro_suministro' => $item['nro_suministro'] ?? null,
                'fecha' => $this->formatearFecha($item['fecha'] ?? null),
                'tipo' => $item['tipo'] ?? null,
                'letra_comprobante' => $item['letra_comprobante'] ?? null,
                'nro_autorizacion' => $item['nro_autorizacion'] ?? null,
                'anio' => $item['anio'] ?? null,
                'mes' => $item['mes'] ?? null,
                // Magnitud sin signo, 2 decimales fijos -- nunca `importe_firmado` (campo propio
                // nuestro, no existe en el contrato real).
                'importe' => number_format(abs((float) ($item['importe'] ?? 0)), 2, '.', ''),
                'debito_credito' => $item['debito_credito'] ?? null,
                'fecha_vencimiento' => $this->formatearFecha($item['fecha_vencimiento'] ?? null),
                'fecha_autorizacion' => $this->formatearFecha($item['fecha_autorizacion'] ?? null),
                'detalle' => $item['detalle'] ?? null,
                'otras_ventas_codigo' => $item['otras_ventas_codigo'] ?? null,
                'imprimir_recibo' => $item['imprimir_recibo'] ?? null,
                'nro_cliente' => $item['nro_cliente'] ?? $recibo->nro_cliente,
            ];
        })->values()->all();
    }

    /**
     * "Documento" que identifica cómo/con qué banco entró el dinero de este Recibo -- lo exige
     * /pagar-otro-documento (ver Documento.class decompilado). `ente_id`/`banco_id` salen de
     * catálogos reales de api-cobranzas-bancos (GET /v1/entes, GET /v1/bancos), configurados
     * en services.cobranzas.documento_ente_id/documento_banco_id -- ya validado que existen
     * antes de llegar acá (ver procesar()).
     *
     * @return array<string, mixed>
     */
    private function construirDocumento(Recibo $recibo): array
    {
        $fecha = $this->formatearFecha($recibo->paid_at?->toDateString());
        $moneda = strtoupper((string) $recibo->currency) === 'USD' ? 'D' : 'B';

        return [
            'ente_id' => (int) config('services.cobranzas.documento_ente_id'),
            'moneda' => $moneda,
            'banco_id' => (int) config('services.cobranzas.documento_banco_id'),
            // Referencia del pago real (SIP/Banco BISA) -- usamos el alias del Recibo, único
            // por diseño (ver PagoQrController::generar()), como número de documento.
            'numero' => $recibo->provider_order_number ?: $recibo->alias,
            'importe' => (float) $recibo->amount,
            'fecha' => $fecha,
            // Sin un "vencimiento" real distinto (el pago ya ocurrió) -- se repite la misma
            // fecha, mismo criterio que "00000000" del cliente real cuando no aplica.
            'fecha_vencimiento' => $fecha,
        ];
    }

    private function formatearFecha(?string $valor): string
    {
        if (blank($valor)) {
            return '00000000';
        }

        try {
            return Carbon::parse($valor)->format('Ymd');
        } catch (\Throwable) {
            return '00000000';
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
