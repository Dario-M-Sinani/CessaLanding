<?php

namespace App\Http\Controllers;

use App\Services\CessaApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsultaDeudaController extends Controller
{
    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request): Response
    {
        $nroCliente = $request->query('nro_cliente');
        $zona = $request->query('zona');
        $manzano = $request->query('manzano');
        $correlativo = $request->query('correlativo');

        $resultado = null;
        $error = null;

        if ($nroCliente || ($zona && $manzano && $correlativo)) {
            try {
                $params = array_filter([
                    'ver_deuda' => 'si',
                    'nro_cliente' => $nroCliente,
                    'zona' => $zona,
                    'manzano' => $manzano,
                    'correlativo' => $correlativo,
                ]);

                $data = $this->apiService->consultaDeuda($params);

                if (isset($data['error'])) {
                    $error = 'No se encontró ningún cliente con los datos ingresados.';
                } else {
                    $pendientes = $data['deuda'] ?? [];

                    $resultado = [
                        'nro_cliente' => $data['nro_cliente'] ?? null,
                        'nombre' => $data['nombre'] ?? null,
                        'direccion' => $data['direccion'] ?? null,
                        'nro_cuenta' => $data['nro_cuenta'] ?? null,
                        'nro_medidor' => $data['nro_medidor'] ?? null,
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
        }

        return Inertia::render('ConsultaDeuda', [
            'filters' => [
                'nro_cliente' => $nroCliente ?? '',
                'zona' => $zona ?? '',
                'manzano' => $manzano ?? '',
                'correlativo' => $correlativo ?? '',
            ],
            'resultado' => $resultado,
            'error' => $error,
        ]);
    }
}
