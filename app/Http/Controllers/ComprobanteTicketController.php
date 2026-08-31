<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Services\Cobranzas\CobranzasBancoService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComprobanteTicketController extends Controller
{
    public function __construct(private readonly CobranzasBancoService $cobranzasService)
    {
    }

    /**
     * Muestra la vista imprimible del comprobante / ticket interno para un Recibo.
     */
    public function show(Request $request, string $alias): View
    {
        $recibo = Recibo::where('alias', $alias)->firstOrFail();

        $factura = $this->obtenerDatosFactura($recibo);
        $qrImage = $this->generarQrDataUri($factura, $recibo);

        return view('recibos.comprobante_ticket', [
            'recibo' => $recibo,
            'factura' => $factura,
            'qrImage' => $qrImage,
        ]);
    }

    /**
     * Obtiene los datos de la factura desde la API del SIIC o los construye del snapshot del Recibo.
     *
     * @return array<string, mixed>
     */
    private function obtenerDatosFactura(Recibo $recibo): array
    {
        // 1. Si ya se facturó en el SIIC, intentamos consultar el JSON oficial
        if ($recibo->cobranzas_uuid && config('services.cobranzas.enabled')) {
            try {
                $doc = $this->cobranzasService->obtenerComprobanteJson($recibo->cobranzas_uuid);

                if (!empty($doc) && isset($doc['nro_factura'])) {
                    $detalle = collect($doc['detalle'] ?? [])->map(function ($item) {
                        return [
                            'descripcion' => $this->sanitizeUtf8($item['descripcion'] ?? $item['detalle'] ?? 'Concepto'),
                            'subtotal' => (float) ($item['subtotal'] ?? $item['precio_unitario'] ?? 0),
                        ];
                    })->all();

                    return [
                        'periodo' => $this->sanitizeUtf8($doc['periodo'] ?? ($recibo->debt_items[0]['detalle'] ?? 'Actual')),
                        'total_pagar' => (float) ($doc['total_pagar'] ?? $recibo->amount),
                        'total_pagar_literal' => $this->sanitizeUtf8($doc['total_pagar_literal'] ?? null),
                        'cliente_nombre' => $this->sanitizeUtf8($doc['cliente_nombre'] ?? $recibo->payer_name),
                        'cliente_cuenta' => $doc['cliente_cuenta'] ?? ($recibo->debt_items[0]['nro_suministro'] ?? 'N/A'),
                        'cliente_codigo' => $doc['cliente_codigo'] ?? $recibo->nro_cliente,
                        'nro_medidor' => $doc['nro_medidor'] ?? 'N/A',
                        'consumo_kwh' => $doc['consumo'] ?? null,
                        'detalle' => $detalle,
                        'nro_factura' => $doc['nro_factura'] ?? null,
                        'codigo_autorizacion' => $doc['codigo_autorizacion'] ?? null,
                        'fecha_cancelacion' => $doc['fecha_cancelacion'] ?? ($recibo->paid_at?->format('d/m/Y H:i') ?? null),
                        'cajero' => $this->sanitizeUtf8($doc['cajero'] ?? 'CESSA VIRTUAL'),
                        'caja' => $doc['caja'] ?? 'WEB',
                        'url_sfe' => $doc['url_sfe'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('comprobante_ticket.siic_error', [
                    'recibo_id' => $recibo->id,
                    'alias' => $recibo->alias,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Fallback: Construir a partir de los datos guardados en el Recibo
        $primerItem = $recibo->debt_items[0] ?? [];
        $periodo = $primerItem['detalle'] ?? ($primerItem['mes'] ? "Mes {$primerItem['mes']}/{$primerItem['anio']}" : 'Servicio');

        $detalleItems = collect($recibo->debt_items ?? [])->map(function ($item) {
            return [
                'descripcion' => $this->sanitizeUtf8($item['detalle'] ?? 'Servicio de Energía Eléctrica'),
                'subtotal' => (float) ($item['importe'] ?? 0),
            ];
        })->all();

        return [
            'periodo' => $this->sanitizeUtf8($periodo),
            'total_pagar' => (float) $recibo->amount,
            'total_pagar_literal' => null,
            'cliente_nombre' => $this->sanitizeUtf8($recibo->payer_name ?: 'Usuario CESSA'),
            'cliente_cuenta' => $primerItem['nro_suministro'] ?? 'N/A',
            'cliente_codigo' => $recibo->nro_cliente,
            'nro_medidor' => 'N/A',
            'consumo_kwh' => null,
            'detalle' => !empty($detalleItems) ? $detalleItems : [
                ['descripcion' => 'Pago por Código QR (' . $recibo->alias . ')', 'subtotal' => (float) $recibo->amount]
            ],
            'nro_factura' => $recibo->provider_order_number ?: $recibo->alias,
            'codigo_autorizacion' => $recibo->cobranzas_uuid,
            'fecha_cancelacion' => $recibo->paid_at?->format('d/m/Y H:i'),
            'cajero' => 'PAGO QR',
            'caja' => 'WEB',
            'url_sfe' => null,
        ];
    }

    /**
     * Limpia y normaliza texto con problemas de codificación UTF-8 / Mojibake del SIIC.
     */
    private function sanitizeUtf8(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        // Detectar si contiene secuencias típicas de UTF-8 doble codificado del SIIC (como 0xC3 seguido de otro byte)
        if (preg_match('/[\xC2\xC3][\x80-\xBF]/', $text) || str_contains($text, 'Ã') || str_contains($text, 'Â')) {
            $converted = @mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
            if (!empty($converted) && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        return $text;
    }

    /**
     * Genera la imagen QR en formato Data URI (Base64 PNG).
     */
    private function generarQrDataUri(array $factura, Recibo $recibo): ?string
    {
        $qrData = $factura['url_sfe'] ?? null;

        if (!$qrData && !empty($factura['codigo_autorizacion']) && !empty($factura['nro_factura'])) {
            $nit = '1000831029'; // NIT CESSA
            $cuf = $factura['codigo_autorizacion'];
            $numero = $factura['nro_factura'];
            $qrData = "https://siat.impuestos.gob.bo/consulta/QR?nit={$nit}&cuf={$cuf}&numero={$numero}&t=2";
        }

        if (!$qrData) {
            $qrData = route('home');
        }

        try {
            $result = (new Builder())->build(
                writer: new PngWriter(),
                data: $qrData,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 200,
                margin: 4,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            );

            return $result->getDataUri();
        } catch (\Throwable $e) {
            Log::error('comprobante_ticket.qr_error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
