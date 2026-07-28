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
            ->get();

        return Inertia::render('Noticias/Index', [
            'newsList' => $newsList,
        ]);
    }

    public function show($id): Response
    {
        $news = News::findOrFail($id);
        $news->increment('hits');

        return Inertia::render('Noticias/Show', [
            'news' => $news,
        ]);
    }
}
