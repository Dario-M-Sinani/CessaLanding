<?php

namespace App\Http\Controllers;

use App\Models\CessaRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NuevaConexionController extends Controller
{
    private const REQUEST_TYPES = [
        'nueva-conexion' => [
            'requestKind' => 'NUEVA_CONEXION',
            'pageBadge' => 'Nuevos Suministros',
            'pageTitle' => 'Solicitud de Nueva Conexión',
            'pageDescription' => 'Completa el formulario para solicitar la instalación de un nuevo medidor de energía eléctrica.',
        ],
        'suspension-servicio' => [
            'requestKind' => 'SUSPENSION',
            'pageBadge' => 'Suspensión de Servicio',
            'pageTitle' => 'Suspensión Temporal o Definitiva',
            'pageDescription' => 'Solicita la suspensión temporal o definitiva del suministro eléctrico de tu inmueble.',
        ],
        'otras-solicitudes' => [
            'requestKind' => 'OTRAS',
            'pageBadge' => 'Otros Trámites',
            'pageTitle' => 'Otras Solicitudes',
            'pageDescription' => 'Registra cualquier otro trámite o solicitud relacionada con tu servicio eléctrico.',
        ],
    ];

    public function index(): Response
    {
        return Inertia::render('NuevaConexion', array_merge(
            self::REQUEST_TYPES['nueva-conexion'],
            ['googleMapsApiKey' => config('services.google_maps.key')],
        ));
    }

    public function suspension(): Response
    {
        return Inertia::render('SuspensionServicio', array_merge(
            self::REQUEST_TYPES['suspension-servicio'],
            ['googleMapsApiKey' => config('services.google_maps.key')],
        ));
    }

    public function otras(): Response
    {
        return Inertia::render('OtrasSolicitudes', array_merge(
            self::REQUEST_TYPES['otras-solicitudes'],
            [
                'googleMapsApiKey' => config('services.google_maps.key'),
                'requestTypeOptions' => self::OTRAS_REQUEST_TYPE_IDS,
            ],
        ));
    }

    // IDs heredados del legacy (tabla `request_types`, no migrada a este puerto todavía)
    // -- se guardan igual para no perder la referencia si más adelante se normaliza.
    private const REQUEST_FORM_IDS = [
        'NUEVO_SUMINISTRO' => 1,
        'SUSPENSION_TEMPORAL' => 2,
        'SUSPENSION_DEFINITIVA' => 2,
        'SUSPENSION_INSPECCION' => 2,
        'OTRAS_SOLICITUDES' => 3,
    ];

    private const REQUEST_TYPE_IDS = [
        'NUEVO_SUMINISTRO' => 1,
        'SUSPENSION_TEMPORAL' => 2,
        'SUSPENSION_DEFINITIVA' => 3,
        'SUSPENSION_INSPECCION' => 4,
    ];

    // Catálogo completo de "Otras Solicitudes" (tabla `request_types`, request_form_id=3
    // en el legacy) -- el usuario elige uno específico, a diferencia de nueva conexión/
    // suspensión donde el tipo se infiere directo de `service_type`.
    public const OTRAS_REQUEST_TYPE_IDS = [
        5 => 'Traslado en la Misma Casa (Cambio de Medidor)',
        6 => 'Traslado en la Misma Casa (Cambio de Acometida)',
        7 => 'Traslado en la Misma Casa (General)',
        8 => 'Cambio de Nombre',
        9 => 'Cambio de Datos del Cliente',
        10 => 'Rehabilitación del Suministro',
        11 => 'Rehabilitación con Inspección',
        12 => 'Modificación de Acometida',
        13 => 'Cambio o Asignación de NIT',
        14 => 'Cambio de Medidor',
        15 => 'Cambio de Acometida',
        16 => 'Cambio de Tarifa',
        17 => 'Incremento de Carga',
        18 => 'Traslado de Domicilio',
        19 => 'Revisión de Medidor',
        20 => 'Reubicación a Nueva Red de la Acometida',
        21 => 'Nuevo Cliente AP',
    ];

    public function store(Request $request)
    {
        $serviceType = (string) $request->input('service_type');
        $isNewConnection = $serviceType === 'NUEVO_SUMINISTRO';
        $isSuspension = str_starts_with($serviceType, 'SUSPENSION');
        $isOtras = $serviceType === 'OTRAS_SOLICITUDES';

        $validated = $request->validate([
            'fullname' => 'required|string|max:180',
            'email' => 'required|email|max:150',
            'document_number' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'],
            'mobile_phone' => 'required|string|max:15',
            'phone' => 'nullable|string|max:15',
            'address' => 'required|string|max:150',
            'zone' => 'required|string|max:150',
            'reference' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'user_type' => ['nullable', Rule::requiredIf($isOtras), Rule::in(['POSSESSOR', 'OWNER'])],
            'service_type' => ['nullable', 'string', Rule::in([
                'NUEVO_SUMINISTRO', 'SUSPENSION_TEMPORAL', 'SUSPENSION_DEFINITIVA', 'SUSPENSION_INSPECCION', 'OTRAS_SOLICITUDES',
            ])],
            'request_type_id' => ['nullable', Rule::requiredIf($isOtras), Rule::in(array_keys(self::OTRAS_REQUEST_TYPE_IDS))],
            'area' => ['nullable', Rule::requiredIf($isNewConnection || $isOtras), Rule::in(['URBAN', 'RURAL'])],
            'consumer_type' => ['nullable', Rule::requiredIf($isNewConnection || $isOtras), Rule::in(['RESIDENTIAL', 'GENERAL', 'INDUSTRIAL'])],
            'phase_type' => ['nullable', Rule::requiredIf($isNewConnection), Rule::in(['MONOPHASE', 'TRIPHASIC'])],
            'url_document_front' => ['nullable', Rule::requiredIf($isNewConnection || $isSuspension || $isOtras), 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'url_document_back' => ['nullable', Rule::requiredIf($isNewConnection || $isSuspension || $isOtras), 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'url_invoice' => ['nullable', Rule::requiredIf($isSuspension || $isOtras), 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'url_last_meter_reading' => ['nullable', Rule::requiredIf($isSuspension), 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'last_meter_reading' => ['nullable', Rule::requiredIf($isSuspension), 'string', 'max:12', 'regex:/^[0-9]+$/'],
        ]);

        $frontPath = 'documents/front_default.jpg';
        $backPath = 'documents/back_default.jpg';
        $invoicePath = null;
        $meterPath = null;

        if ($request->hasFile('url_document_front')) {
            $frontPath = $request->file('url_document_front')->store('requests', 'public');
        }

        if ($request->hasFile('url_document_back')) {
            $backPath = $request->file('url_document_back')->store('requests', 'public');
        }

        if ($request->hasFile('url_invoice')) {
            $invoicePath = $request->file('url_invoice')->store('requests', 'public');
        }

        if ($request->hasFile('url_last_meter_reading')) {
            $meterPath = $request->file('url_last_meter_reading')->store('requests', 'public');
        }

        try {
            $cessaRequest = CessaRequest::create([
                'fullname' => $validated['fullname'],
                'email' => $validated['email'],
                'document_number' => $validated['document_number'],
                'mobile_phone' => $validated['mobile_phone'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'],
                'zone' => $validated['zone'],
                'reference' => $validated['reference'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                // El titular de una nueva conexión siempre es el "propietario" (igual que el legacy);
                // en "otras solicitudes" lo elige el cliente (poseedor/titular); en suspensión no aplica.
                'user_type' => $isNewConnection ? 'OWNER' : ($validated['user_type'] ?? 'RESIDENCIAL'),
                'service_type' => $validated['service_type'] ?? 'NUEVO_SUMINISTRO',
                'area' => $validated['area'] ?? null,
                'consumer_type' => $validated['consumer_type'] ?? null,
                'phase_type' => $validated['phase_type'] ?? null,
                'url_document_front' => $frontPath,
                'url_document_back' => $backPath,
                'url_invoice' => $invoicePath,
                'url_last_meter_reading' => $meterPath,
                'last_meter_reading' => $validated['last_meter_reading'] ?? null,
                'status' => 'PENDIENTE',
                'send_date' => now(),
                'created_by' => 'PORTAL_WEB',
                'request_form_id' => self::REQUEST_FORM_IDS[$serviceType] ?? 1,
                'request_type_id' => $isOtras ? (int) $validated['request_type_id'] : (self::REQUEST_TYPE_IDS[$serviceType] ?? 1),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', 'No se pudo registrar tu solicitud. Por favor, intentá nuevamente en unos minutos.');
        }

        return redirect()->back()->with('success', '¡Solicitud N° #' . $cessaRequest->id . ' registrada exitosamente! Nos contactaremos al número ' . $validated['mobile_phone'] . ' para coordinar la atención de tu trámite.');
    }
}
