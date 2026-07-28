<?php

namespace App\Http\Controllers;

use App\Models\CessaRequest;
use App\Services\CessaApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuscarTramiteController extends Controller
{
    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request): Response
    {
        $nroSolicitud = $request->query('nro_solicitud');
        $nroDocumento = $request->query('nro_documento');

        $resultado = null;
        $error = null;

        if ($nroSolicitud || $nroDocumento) {
            // Check local Eloquent DB first
            try {
                $query = CessaRequest::query();
                if ($nroSolicitud) {
                    $query->where('id', $nroSolicitud);
                }
                if ($nroDocumento) {
                    $query->where('document_number', 'like', '%' . $nroDocumento . '%');
                }

                $localRequest = $query->first();

                if ($localRequest) {
                    $resultado = [
                        'id' => $localRequest->id,
                        'fullname' => $localRequest->fullname,
                        'document_number' => $localRequest->document_number,
                        'service_type' => $localRequest->service_type ?? 'NUEVO_SUMINISTRO',
                        'status' => $localRequest->status,
                        'send_date' => $localRequest->send_date ? $localRequest->send_date->format('d/m/Y H:i') : null,
                        'observation' => $localRequest->observation ?? 'Su solicitud está siendo evaluada por nuestro personal técnico.',
                    ];
                }
            } catch (\Exception $e) {
                // Ignore DB error if DB offline
            }

            if (!$resultado) {
                $apiUrl = config('services.cessa_api.url');
                if (!empty($apiUrl)) {
                    try {
                        $resultado = $this->apiService->buscarTramite([
                            'nro_solicitud' => $nroSolicitud,
                            'nro_documento' => $nroDocumento,
                        ]);
                    } catch (\Exception $e) {
                        $error = 'No se encontró información correspondiente a los datos ingresados.';
                    }
                } else {
                    // Fallback Mock data for testing
                    $resultado = [
                        'id' => $nroSolicitud ?? '1025',
                        'fullname' => 'JUAN PEREZ MAMANI',
                        'document_number' => $nroDocumento ?? '6543210 CB',
                        'service_type' => 'NUEVO_SUMINISTRO_RESIDENCIAL',
                        'status' => 'EN_PROCESO',
                        'send_date' => date('d/m/Y H:i'),
                        'observation' => 'Solicitud aprobada preliminarmente. Técnico asignado para inspección de puesto de medición.',
                    ];
                }
            }
        }

        return Inertia::render('BuscarTramite', [
            'filters' => [
                'nro_solicitud' => $nroSolicitud ?? '',
                'nro_documento' => $nroDocumento ?? '',
            ],
            'resultado' => $resultado,
            'error' => $error,
        ]);
    }
}
