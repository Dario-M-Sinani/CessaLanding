<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcesosController extends Controller
{
    public function index(): Response
    {
        $publications = Publication::where('published', 'S')
            ->with('documents')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Procesos/Index', [
            'publications' => $publications,
            'typeLabels' => Publication::getTypes(),
        ]);
    }
}
