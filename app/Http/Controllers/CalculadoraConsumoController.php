<?php

namespace App\Http\Controllers;

use App\Services\CessaApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalculadoraConsumoController extends Controller
{
    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request): Response
    {
        $categories = [];
        $periods = [];

        try {
            $categories = collect($this->apiService->getCategories())
                ->map(fn ($cat) => [
                    'codigo' => $cat['codigo'],
                    'nombre' => $cat['nombre'],
                    'demanda' => $cat['demanda'] ?? '0',
                ])
                ->values()
                ->all();

            $periods = collect($this->apiService->getPeriods())
                ->map(fn ($p) => [
                    // The SIIC API expects the period back as its fecha_vigencia (YYYYMMDD), not its numeric id.
                    'fecha_vigencia' => $p['fecha_vigencia'],
                    'label' => $this->formatPeriodLabel($p['fecha_vigencia']),
                ])
                ->values()
                ->all();
        } catch (\Exception $e) {
            // API unreachable: the page still renders, the form will just have empty selects.
        }

        return Inertia::render('CalculadoraConsumo', [
            'categories' => $categories,
            'periods' => $periods,
        ]);
    }

    public function calcular(Request $request)
    {
        $request->validate([
            'consumo' => 'required|numeric|min:0',
            'categoria' => 'required|string',
            'periodo' => 'required|string',
            'demanda' => 'nullable|numeric',
        ]);

        try {
            $params = [
                'consumo' => $request->consumo,
                'categoria' => $request->categoria,
                'periodo' => $request->periodo,
            ];

            if ($request->filled('demanda')) {
                $params['demanda'] = $request->demanda;
            }

            $data = $this->apiService->calculoConsumo($params);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular el consumo con los datos ingresados.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'importe_energia' => $data['importe_energia'] ?? '0.00',
                    'importe_demanda' => $data['importe_demanda'] ?? '0.00',
                    'total' => $data['total'] ?? '0.00',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular la tarifa en el servidor comercial.',
            ], 500);
        }
    }

    protected function formatPeriodLabel(string $fechaVigencia): string
    {
        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $year = substr($fechaVigencia, 0, 4);
        $month = (int) substr($fechaVigencia, 4, 2);

        return ($months[$month - 1] ?? $month) . '/' . $year;
    }
}
