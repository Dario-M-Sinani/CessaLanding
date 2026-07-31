<?php

namespace App\Http\Controllers;

use App\Services\CessaApiService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class EstructuraTarifariaController extends Controller
{
    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(): Response
    {
        $periods = [];

        try {
            $periods = collect($this->apiService->getPeriods())
                ->map(fn ($p) => [
                    'id' => $p['id'],
                    'fecha_vigencia' => $p['fecha_vigencia'],
                    'label' => $this->formatPeriodLabel($p['fecha_vigencia']),
                ])
                ->values()
                ->all();
        } catch (\Exception $e) {
            // API inalcanzable: la página igual renderiza, el selector queda vacío.
        }

        return Inertia::render('EstructuraTarifaria', [
            'periods' => $periods,
        ]);
    }

    public function detalle(string $id): JsonResponse
    {
        try {
            $data = $this->apiService->getPeriods($id);

            if (empty($data) || isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron tarifas para ese periodo.',
                ], 404);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con el sistema comercial SIIC.',
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
