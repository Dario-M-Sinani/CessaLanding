<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function show(string $alias): Response
    {
        $content = Content::where('alias', $alias)->where('published', 'S')->firstOrFail();
        $content->increment('hits');

        return Inertia::render('Content/Show', [
            'content' => $content,
            // Catálogo del widget de materiales: una sola fuente (la misma de la etiqueta imprimible).
            'instalaciones' => $alias === 'nuevas-instalaciones' ? InstalacionMaterialesController::getCatalogo() : null,
        ]);
    }
}
