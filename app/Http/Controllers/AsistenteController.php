<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Backend del mini asistente del footer (widget flotante, ver
 * resources/js/Components/MiniAsistente.vue): por ahora solo expone las FAQs
 * ya publicadas para que el widget las busque del lado del cliente, más los
 * accesos rápidos que están hardcodeados en el propio componente Vue (no
 * hay nada que administrar de esos, son siempre los mismos). Sin IA -- ver
 * ESTADO_SEGURIDAD_MIGRACION.md §-1undevicies para la propuesta original y
 * por qué se decidió empezar por acá antes que un chatbot real.
 */
class AsistenteController extends Controller
{
    public function faqs(): JsonResponse
    {
        $faqs = Cache::remember('asistente.faqs', 300, function () {
            return Faq::where('published', 'S')
                ->orderBy('position', 'asc')
                ->get(['id', 'question', 'answer']);
        });

        return response()->json(['faqs' => $faqs]);
    }
}
