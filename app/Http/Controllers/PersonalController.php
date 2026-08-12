<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\PersonalCategoria;
use Inertia\Inertia;
use Inertia\Response;

class PersonalController extends Controller
{
    public function show(string $categoria): Response
    {
        $categoriaModel = PersonalCategoria::where('alias', $categoria)->first();

        abort_unless($categoriaModel, 404);

        $personal = Personal::where('personal_categoria_id', $categoriaModel->id)
            ->where('published', 'S')
            ->orderBy('position')
            ->get(['id', 'nombre', 'ci', 'tipo_sangre', 'celular', 'descripcion', 'foto']);

        return Inertia::render('Personal/Show', [
            'categoriaActual' => $categoriaModel->alias,
            // pluck(nombre, alias) en vez de una lista de objetos -- Personal/Show.vue ya
            // esperaba un objeto plano {alias: nombre} (antes venía de Personal::CATEGORIAS),
            // así no hizo falta tocar la vista al pasar a la tabla dinámica.
            'categorias' => PersonalCategoria::orderBy('position')->pluck('nombre', 'alias'),
            'personal' => $personal,
        ]);
    }
}
