<?php

namespace App\Http\Controllers;

use App\Services\CessaApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * "Tus últimas facturas" de Consulta de Deuda: las últimas 12 facturas ya pagadas del cliente
 * (por cualquier canal: cajas, bancos, QR, app), leídas del SIIC, con su PDF real.
 *
 * El PDF trae nombre, CI/NIT y dirección, así que nada de esto se pide por nro_cliente: solo
 * sirve para la cuenta que se acaba de verificar en ConsultaDeudaController::consultar (nro de
 * cliente + N° de Cuenta, guardada en sesión). El PDF se pide por índice dentro de la lista que
 * se guardó en la sesión, nunca con datos de comprobante que mande el navegador.
 */
class FacturasClienteController extends Controller
{
    public const SESION_VERIFICADO = 'consulta_deuda.verificado';

    private const SESION_FACTURAS = 'consulta_deuda.facturas';

    private const LIMITE = 12;

    // Se piden más al SIIC porque algunos comprobantes pagados no tienen PDF (ver TIPOS_CON_PDF)
    // y se descartan; así igual se llega a mostrar 12.
    private const PEDIR_AL_SIIC = 30;

    // Tipos (COMPTPO) que POST /v1/comprobantes del SIIC sabe imprimir: facturas de servicio y de
    // otras ventas (1, 3), recibos (2, 5) y conciliaciones (30, 31). Con cualquier otro (p.ej. 80,
    // "NC. DEVOLUCION DE GARANTIA") el SIIC revienta con "Undefined index: tipo" (HTTP 500).
    private const TIPOS_CON_PDF = [1, 2, 3, 5, 30, 31];

    // Cuánto dura la verificación de la cuenta sin usarla; cada uso la renueva.
    private const VIGENCIA_MINUTOS = 30;

    // Campos con los que el SIIC identifica un comprobante (los pide POST /v1/comprobantes).
    private const CLAVE_COMPROBANTE = [
        'codigo_sucursal', 'nro_comprobante', 'nro_suministro', 'fecha', 'tipo',
        'letra_comprobante', 'nro_autorizacion', 'nro_cliente',
    ];

    public function __construct(private readonly CessaApiService $apiService)
    {
    }

    public static function marcarVerificado(Request $request, string $nroCliente): void
    {
        $request->session()->put(self::SESION_VERIFICADO, ['nro_cliente' => $nroCliente, 'at' => now()->timestamp]);
        $request->session()->forget(self::SESION_FACTURAS);
    }

    public function listado(Request $request): JsonResponse
    {
        $nroCliente = $this->clienteVerificado($request);

        if (! $nroCliente) {
            return response()->json(['message' => 'Volvé a consultar tu deuda para ver tus facturas.'], 403);
        }

        try {
            $items = $this->cargarLista($request, $nroCliente);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'No se pudieron cargar tus facturas en este momento.'], 503);
        }

        return response()->json([
            'facturas' => array_map(fn (array $item, int $i) => [
                'indice' => $i,
                'detalle' => trim((string) ($item['detalle'] ?? '')),
                'importe' => number_format((float) ($item['importe'] ?? 0), 2, '.', ''),
                'pagado_el' => $this->fechaPago($item),
            ], $items, array_keys($items)),
        ]);
    }

    public function pdf(Request $request, int $indice): Response
    {
        $nroCliente = $this->clienteVerificado($request);

        if (! $nroCliente) {
            return $this->paginaAviso('Tu consulta venció', 'Por seguridad, volvé a consultar tu deuda con tu N° de Cliente y N° de Cuenta para descargar tus facturas.', 403);
        }

        $lista = $request->session()->get(self::SESION_FACTURAS);

        // La lista de la sesión pudo perderse (otra consulta en la misma sesión, otra pestaña):
        // con la cuenta todavía verificada, se vuelve a pedir en vez de rebotar al cliente.
        if (($lista['nro_cliente'] ?? null) !== $nroCliente) {
            try {
                $this->cargarLista($request, $nroCliente);
                $lista = $request->session()->get(self::SESION_FACTURAS);
            } catch (\Throwable $e) {
                report($e);

                return $this->paginaAviso('No pudimos generar tu factura', 'El sistema comercial no responde en este momento. Intentá de nuevo en unos minutos.', 503);
            }
        }

        if (! isset($lista['items'][$indice])) {
            return $this->paginaAviso('Factura no encontrada', 'Volvé a la consulta de deuda y elegí la factura de nuevo.', 404);
        }

        $item = $lista['items'][$indice];

        try {
            $pdf = $this->apiService->comprobantePdf($item);
        } catch (\Throwable $e) {
            report($e);
            $pdf = null;
        }

        if (! $pdf) {
            Log::warning('facturas_cliente.pdf_fallo', ['nro_cliente' => $nroCliente, 'comprobante' => $item['nro_comprobante'] ?? null, 'tipo' => $item['tipo'] ?? null]);

            return $this->paginaAviso('No pudimos generar tu factura', 'El sistema comercial no pudo generar esta factura en este momento. Intentá de nuevo en unos minutos o acercate a oficinas de CESSA.', 503);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura-cessa-'.$nroCliente.'-'.$item['nro_comprobante'].'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Pide al SIIC los últimos comprobantes pagados, deja solo los que tienen PDF (hasta 12) y
     * guarda sus claves en la sesión para que pdf() los pida por índice.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cargarLista(Request $request, string $nroCliente): array
    {
        $items = collect($this->apiService->ultimosPagos($nroCliente, self::PEDIR_AL_SIIC))
            ->filter(fn (array $item) => in_array((int) ($item['tipo'] ?? 0), self::TIPOS_CON_PDF, true))
            ->take(self::LIMITE)
            ->values()
            ->all();

        $claves = array_map(fn (array $item) => array_intersect_key($item, array_flip(self::CLAVE_COMPROBANTE)), $items);
        $request->session()->put(self::SESION_FACTURAS, ['nro_cliente' => $nroCliente, 'items' => $claves]);

        return $items;
    }

    private function clienteVerificado(Request $request): ?string
    {
        $verificado = $request->session()->get(self::SESION_VERIFICADO);

        if (! $verificado || now()->timestamp - ($verificado['at'] ?? 0) > self::VIGENCIA_MINUTOS * 60) {
            return null;
        }

        // Cada uso renueva la vigencia (mientras el cliente esté mirando/bajando sus facturas).
        $request->session()->put(self::SESION_VERIFICADO.'.at', now()->timestamp);

        return $verificado['nro_cliente'];
    }

    /**
     * El PDF se abre en una pestaña nueva: si algo falla, el cliente ve una página con un mensaje
     * claro en vez de un JSON o el error genérico de Cloudflare.
     */
    private function paginaAviso(string $titulo, string $mensaje, int $status): Response
    {
        $titulo = e($titulo);
        $mensaje = e($mensaje);
        $volver = e(route('consulta-deuda'));

        $html = <<<HTML
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$titulo} · CESSA</title></head>
<body style="margin:0;font-family:system-ui,sans-serif;background:#f8fafc;color:#0f172a;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:16px">
<div style="max-width:420px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;text-align:center;box-shadow:0 4px 16px rgba(15,23,42,.06)">
<p style="margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#1e3a8a">CESSA</p>
<h1 style="margin:0 0 10px;font-size:20px">{$titulo}</h1>
<p style="margin:0 0 20px;font-size:14px;color:#475569;line-height:1.5">{$mensaje}</p>
<a href="{$volver}" style="display:inline-block;background:#1e3a8a;color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:10px 18px;border-radius:10px">Ir a Consulta de Deuda</a>
</div></body></html>
HTML;

        return response($html, $status, ['Content-Type' => 'text/html; charset=utf-8', 'Cache-Control' => 'no-store']);
    }

    /** "20260820" + "08:20:57" -> "20/08/2026 08:20". */
    private function fechaPago(array $item): ?string
    {
        $fecha = (string) ($item['pago_fecha'] ?? '');

        if (strlen($fecha) !== 8) {
            return null;
        }

        return substr($fecha, 6, 2).'/'.substr($fecha, 4, 2).'/'.substr($fecha, 0, 4)
            .(! empty($item['pago_hora']) ? ' '.substr((string) $item['pago_hora'], 0, 5) : '');
    }
}
