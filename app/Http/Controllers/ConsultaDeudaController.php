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
                    'pendientes' => $data['deuda'] ?? [],
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
