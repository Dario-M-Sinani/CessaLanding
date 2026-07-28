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
            ->get();

        return Inertia::render('Informacion/CortesProgramados', [
            'outages' => $outages,
        ]);
    }

    public function documentos(): Response
    {
        $documents = Document::where('published', 'S')
            ->orderBy('position', 'asc')
            ->get();

        return Inertia::render('Informacion/Documentos', [
            'documents' => $documents,
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
}
