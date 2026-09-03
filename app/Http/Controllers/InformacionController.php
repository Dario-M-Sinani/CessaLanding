<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Faq;
use App\Models\ScheduledOutage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InformacionController extends Controller
{
    public function cortesProgramados(): Response
    {
        $outages = ScheduledOutage::where('published', 'S')
            ->orderBy('execution_date', 'desc')
            ->paginate(6)
            ->withQueryString();

        return Inertia::render('Informacion/CortesProgramados', [
            'outages' => $outages,
        ]);
    }

    public function documentos(Request $request): Response
    {
        $search = $request->string('q')->trim()->toString();

        // 'position' es el orden manual dentro de cada Publicación/proceso (la mayoría de
        // filas empatan en 1) -- sin desempate, MySQL no garantiza qué fila empatada gana,
        // así que un documento recién subido no aparecía primero de forma confiable. Mismo
        // fix que la Galería de Videos (ver ESTADO_SEGURIDAD_MIGRACION.md §-1quinquies):
        // el orden manual sigue mandando, y entre empates gana el más reciente (id más alto).
        $documents = Document::where('published', 'S')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy('position', 'asc')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Informacion/Documentos', [
            'documents' => $documents,
            'filters' => ['q' => $search],
        ]);
    }

    public function faqs(): Response
    {
        $faqs = Faq::where('published', 'S')
            ->orderBy('position', 'asc')
            ->get();

        return Inertia::render('Informacion/Faqs', [
            'faqs' => $faqs,
        ]);
    }

    public function consejosSeguridad(): Response
    {
        return Inertia::render('Informacion/ConsejosSeguridad');
    }
}
