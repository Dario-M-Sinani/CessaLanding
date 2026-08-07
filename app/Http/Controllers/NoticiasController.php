<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoticiasController extends Controller
{
    public function index(): Response
    {
        $newsList = News::where('published', 'S')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Noticias/Index', [
            'newsList' => $newsList,
        ]);
    }

    public function show($id): Response
    {
        $news = News::where('published', 'S')->findOrFail($id);
        $news->increment('hits');

        $orderedIds = News::where('published', 'S')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->pluck('id')
            ->toArray();

        $position = array_search($news->id, $orderedIds, true);
        $previous = $position !== false && $position > 0
            ? News::find($orderedIds[$position - 1], ['id', 'title'])
            : null;
        $next = $position !== false && $position < count($orderedIds) - 1
            ? News::find($orderedIds[$position + 1], ['id', 'title'])
            : null;

        return Inertia::render('Noticias/Show', [
            'news' => $news,
            'previous' => $previous,
            'next' => $next,
        ]);
    }
}
