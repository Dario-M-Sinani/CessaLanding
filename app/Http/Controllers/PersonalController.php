<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use Inertia\Inertia;
use Inertia\Response;

class PersonalController extends Controller
{
    public function show(string $categoria): Response
    {
        abort_unless(array_key_exists($categoria, Personal::CATEGORIAS), 404);

        $personal = Personal::where('categoria', $categoria)
            ->where('published', 'S')
            ->orderBy('position')
            ->get(['id', 'nombre', 'ci', 'tipo_sangre', 'celular', 'descripcion', 'foto']);

        return Inertia::render('Personal/Show', [
            'categoriaActual' => $categoria,
            'categorias' => Personal::CATEGORIAS,
            'personal' => $personal,
        ]);
    }
}
