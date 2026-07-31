<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Faq;
use App\Models\News;
use App\Models\Publication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusquedaController extends Controller
{
    protected const LIMIT_PER_TYPE = 8;

    public function index(Request $request): Response
    {
        $q = $request->string('q')->trim()->toString();

        $resultados = $q === '' ? [] : $this->buscar($q);

        return Inertia::render('Busqueda', [
            'q' => $q,
            'resultados' => $resultados,
        ]);
    }

    protected function buscar(string $q): array
    {
        $like = '%'.$q.'%';

        $noticias = News::where('published', 'S')
            ->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('summary', 'like', $like))
            ->orderByDesc('created_at')
            ->limit(self::LIMIT_PER_TYPE)
            ->get(['id', 'title', 'summary'])
            ->map(fn (News $n) => [
                'title' => $n->title,
                'snippet' => $n->summary,
                'url' => "/noticias/{$n->id}",
            ]);

        $publicaciones = Publication::where('published', 'S')
            ->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->orderByDesc('created_at')
            ->limit(self::LIMIT_PER_TYPE)
            ->get(['id', 'title', 'description'])
            ->map(fn (Publication $p) => [
                'title' => $p->title,
                'snippet' => $p->description,
                'url' => '/procesos',
            ]);

        $faqs = Faq::where('published', 'S')
            ->where(fn ($query) => $query->where('question', 'like', $like)->orWhere('answer', 'like', $like))
            ->orderBy('position')
            ->limit(self::LIMIT_PER_TYPE)
            ->get(['id', 'question', 'answer'])
            ->map(fn (Faq $f) => [
                'title' => $f->question,
                'snippet' => html_entity_decode(strip_tags($f->answer ?? '')),
                'url' => '/informacion/faqs',
            ]);

        $contenidos = Content::where('published', 'S')
            ->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('summary', 'like', $like))
            ->orderBy('position')
            ->limit(self::LIMIT_PER_TYPE)
            ->get(['id', 'title', 'summary', 'alias'])
            ->map(fn (Content $c) => [
                'title' => $c->title,
                'snippet' => $c->summary,
                'url' => "/contenido/{$c->alias}",
            ]);

        return array_filter([
            'Noticias' => $noticias->values()->all(),
            'Licitaciones y Convocatorias' => $publicaciones->values()->all(),
            'Preguntas Frecuentes' => $faqs->values()->all(),
            'Información' => $contenidos->values()->all(),
        ], fn (array $items) => count($items) > 0);
    }
}
