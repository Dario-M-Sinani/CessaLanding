<?php

namespace App\Http\Controllers;

use App\Services\CessaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ConsultaDeudaController extends Controller
{
    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(): Response
    {
        return Inertia::render('ConsultaDeuda', [
            'filters' => ['nro_cliente' => '', 'zona' => '', 'manzano' => '', 'correlativo' => ''],
            'resultado' => null,
            'error' => null,
        ]);
    }

    public function consultar(Request $request): Response
    {
        // Ambos datos son obligatorios siempre: ya no existe un nivel "básico" con
        // solo nro_cliente. El N° de Cuenta (impreso en la factura física, no
        // compartido entre medidores de un mismo titular como sí lo está el CI) es
        // el segundo factor obligatorio -- sin los dos, no se libera nada.
        $validated = $request->validate([
            'nro_cliente' => ['required', 'digits_between:1,10'],
            'zona' => ['required', 'digits_between:1,3'],
            'manzano' => ['required', 'digits_between:1,4'],
            'correlativo' => ['required', 'digits_between:1,6'],
        ]);

        $nroCliente = $validated['nro_cliente'];
        $zona = $validated['zona'];
        $manzano = $validated['manzano'];
        $correlativo = $validated['correlativo'];

        $resultado = null;
        $error = null;

        try {
            $data = $this->apiService->consultaDeuda([
                'nro_cliente' => $nroCliente,
                'ver_deuda' => 'si',
            ]);

            if (isset($data['error']) || empty($data['nro_cliente'])) {
                $error = 'No se encontró ningún abonado con ese número.';
            } elseif (
                // SIIC devuelve zona/manzano/correlativo sin ceros a la izquierda,
                // pero la factura física los imprime con ceros (ej. "05-059-07421").
                // Se compara como número para que ambos formatos coincidan.
                (int) ($data['zona'] ?? -1) !== (int) $zona
                || (int) ($data['manzano'] ?? -1) !== (int) $manzano
                || (int) ($data['correlativo'] ?? -1) !== (int) $correlativo
            ) {
                // El N° de Cuenta no corresponde a ese nro_cliente: mensaje genérico,
                // no se distingue cuál dato falló (evita usar la respuesta como oráculo).
                Log::warning('consulta_deuda.cuenta_no_coincide', [
                    'ip' => $request->ip(),
                    'nro_cliente' => $nroCliente,
                ]);
                $error = 'Los datos ingresados no coinciden con ningún abonado registrado.';
            } else {
                // Del más antiguo al más reciente -- SIIC exige pagar en ese orden (no se
                // puede saltar un mes viejo para pagar solo el último), así que la tabla y
                // el selector de "cuántos meses pagar" del pago QR usan el mismo orden.
                $pendientes = collect($data['deuda'] ?? [])
                    ->sortBy([
                        fn ($a, $b) => ((int) $a['anio']) <=> ((int) $b['anio']),
                        fn ($a, $b) => ((int) $a['mes']) <=> ((int) $b['mes']),
                    ])
                    ->values()
                    // No se confía en que SIIC ya mande `importe` con el signo correcto para
                    // las conciliaciones (no hay forma de confirmarlo sin un caso real en
                    // producción) -- se trata `importe` como magnitud y el signo se deriva
                    // siempre de `debito_credito`, que sí es un campo explícito. Se manda
                    // aparte como `importe_firmado` para no pisar el dato crudo de SIIC.
                    //
                    // `debito_credito` NO distingue factura de conciliación -- solo el signo.
                    // Confirmado con cuentas reales: hay conciliaciones "NC." (Nota de
                    // Crédito, débito_credito=CREDITO, restan) Y conciliaciones "ND." (Nota de
                    // Débito, debito_credito=DEBITO, suman) -- estas últimas comparten
                    // debito_credito con una factura real y quedaban mal etiquetadas. El
                    // discriminador real es el texto de `detalle` ("... CONCILIACIÓN ...").
                    ->map(function (array $item) {
                        $magnitud = abs((float) ($item['importe'] ?? 0));
                        $item['importe_firmado'] = ($item['debito_credito'] ?? 'DEBITO') === 'CREDITO' ? -$magnitud : $magnitud;
                        $item['es_conciliacion'] = str_contains(mb_strtoupper($item['detalle'] ?? ''), 'CONCILIA');

                        return $item;
                    })
                    ->all();

                // nro_cliente + N° de Cuenta verificados contra SIIC -> detalle completo.
                $resultado = [
                    'nivel' => 'completo',
                    'nro_cliente' => $data['nro_cliente'],
                    'nombre' => $data['nombre'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'nro_cuenta' => $data['nro_cuenta'] ?? null,
                    'estado_codigo' => $data['estado_codigo'] ?? null,
                    'estado_descripcion' => $data['estado_descripcion'] ?? null,
                    'categoria_descripcion' => $data['categoria_descripcion'] ?? null,
                    'pendientes' => $pendientes,
                    'total_deuda' => number_format((float) ($data['deuda_total'] ?? 0), 2, '.', ''),
                ];
            }
        } catch (\Exception $e) {
            $error = 'No se pudo conectar con el sistema comercial SIIC. Intente más tarde.';
        }

        return Inertia::render('ConsultaDeuda', [
            'filters' => [
                'nro_cliente' => $nroCliente,
                'zona' => $zona ?? '',
                'manzano' => $manzano ?? '',
                'correlativo' => $correlativo ?? '',
            ],
            'resultado' => $resultado,
            'error' => $error,
        ]);
    }
}
