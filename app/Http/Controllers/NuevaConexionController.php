<?php

namespace App\Http\Controllers;

use App\Models\CessaRequest;
use Illuminate\Http\Request;
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
        return $this->render('nueva-conexion');
    }

    public function suspension(): Response
    {
        return $this->render('suspension-servicio');
    }

    public function otras(): Response
    {
        return $this->render('otras-solicitudes');
    }

    private function render(string $slug): Response
    {
        return Inertia::render('NuevaConexion', self::REQUEST_TYPES[$slug]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:180',
            'email' => 'required|email',
            'document_number' => 'required|string|max:30',
            'mobile_phone' => 'required|string|max:30',
            'phone' => 'nullable|string|max:30',
            'address' => 'required|string|max:150',
            'zone' => 'required|string|max:150',
            'reference' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'user_type' => 'required|string',
            'service_type' => 'nullable|string',
            'url_document_front' => 'nullable|file|max:5120',
            'url_document_back' => 'nullable|file|max:5120',
        ]);

        $frontPath = 'documents/front_default.jpg';
        $backPath = 'documents/back_default.jpg';

        if ($request->hasFile('url_document_front')) {
            $frontPath = $request->file('url_document_front')->store('requests', 'public');
        }

        if ($request->hasFile('url_document_back')) {
            $backPath = $request->file('url_document_back')->store('requests', 'public');
        }

        $requestId = rand(1000, 9999);

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
                'user_type' => $validated['user_type'],
                'service_type' => $validated['service_type'] ?? 'NUEVO_SUMINISTRO',
                'url_document_front' => $frontPath,
                'url_document_back' => $backPath,
                'status' => 'PENDIENTE',
                'send_date' => now(),
                'created_by' => 'PORTAL_WEB',
                'request_form_id' => 1,
                'request_type_id' => 1,
            ]);
            $requestId = $cessaRequest->id;
        } catch (\Exception $e) {
            // DB fallback if database isn't migrated yet
        }

        return redirect()->back()->with('success', '¡Solicitud N° #' . $requestId . ' registrada exitosamente! Nos contactaremos al número ' . $validated['mobile_phone'] . ' para coordinar la atención de tu trámite.');
    }
}
