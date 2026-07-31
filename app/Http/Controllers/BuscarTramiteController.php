<?php

namespace App\Http\Controllers;

use App\Models\CessaRequest;
use App\Services\CessaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BuscarTramiteController extends Controller
{
    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(): Response
    {
        return Inertia::render('BuscarTramite', [
            'filters' => ['nro_solicitud' => '', 'nro_documento' => ''],
            'resultado' => null,
            'error' => null,
        ]);
    }

    public function buscar(Request $request): Response
    {
        // El N° de solicitud es un id autoincremental (fácil de adivinar/enumerar), por eso
        // siempre se exige junto al documento de identidad del titular: uno solo nunca alcanza.
        $validated = $request->validate([
            'nro_solicitud' => ['required', 'string', 'max:20'],
            'nro_documento' => ['required', 'string', 'max:20'],
        ]);

        $nroSolicitud = $validated['nro_solicitud'];
        $nroDocumento = $validated['nro_documento'];

        $resultado = null;
        $error = null;

        try {
            // Coincidencia exacta, no 'like' parcial: un match parcial facilita enumerar
            // documentos probando fragmentos de CI.
            $localRequest = CessaRequest::query()
                ->where('id', $nroSolicitud)
                ->where('document_number', $nroDocumento)
                ->first();

            if ($localRequest) {
                $resultado = [
                    'id' => $localRequest->id,
                    'fullname' => $localRequest->fullname,
                    'service_type' => $localRequest->service_type ?? 'NUEVO_SUMINISTRO',
                    'status' => $localRequest->status,
                    'send_date' => $localRequest->send_date ? $localRequest->send_date->format('d/m/Y H:i') : null,
                    'observation' => $localRequest->observation ?? 'Su solicitud está siendo evaluada por nuestro personal técnico.',
                ];
            } else {
                $apiUrl = config('services.cessa_api.url');

                if (!empty($apiUrl)) {
                    $data = $this->apiService->buscarTramite([
                        'nro_solicitud' => $nroSolicitud,
                        'nro_documento' => $nroDocumento,
                    ]);

                    if (empty($data) || isset($data['error'])) {
                        $error = 'No se encontró ningún trámite con los datos ingresados.';
                    } else {
                        // El backend arma su propia salida; nunca reenvía el JSON crudo de SIIC.
                        $resultado = [
                            'id' => $data['id'] ?? $nroSolicitud,
                            'fullname' => $data['fullname'] ?? null,
                            'service_type' => $data['service_type'] ?? null,
                            'status' => $data['status'] ?? null,
                            'send_date' => $data['send_date'] ?? null,
                            'observation' => $data['observation'] ?? null,
                        ];
                    }
                } else {
                    $error = 'No se encontró ningún trámite con los datos ingresados.';
                }
            }
        } catch (\Exception $e) {
            $error = 'No se pudo consultar el estado del trámite. Intente más tarde.';
        }

        if (!$resultado && !$error) {
            $error = 'No se encontró ningún trámite con los datos ingresados.';
        }

        Log::info('buscar_tramite.intento', [
            'ip' => $request->ip(),
            'encontrado' => (bool) $resultado,
        ]);

        return Inertia::render('BuscarTramite', [
            'filters' => [
                'nro_solicitud' => $nroSolicitud,
                'nro_documento' => $nroDocumento,
            ],
            'resultado' => $resultado,
            'error' => $error,
        ]);
    }
}
