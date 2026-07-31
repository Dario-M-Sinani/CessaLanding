<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Document;
use App\Models\News;
use App\Models\ScheduledOutage;
use App\Models\Video;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $outages = ScheduledOutage::where('published', 'S')
            ->whereDate('execution_date', '>=', now()->toDateString())
            ->orderBy('execution_date')
            ->limit(3)
            ->get();

        $documents = Document::where('published', 'S')
            ->orderBy('position')
            ->limit(4)
            ->get();

        $consejos = Content::where('alias', 'consejos-de-seguridad')
            ->where('published', 'S')
            ->first();

        $video = Video::where('published', 'S')->orderBy('position')->first();

        $popupNews = News::where('published', 'S')
            ->where('popup', true)
            ->orderBy('created_at', 'desc')
            ->first();

        return Inertia::render('Home', [
            'outages' => $outages,
            'documents' => $documents,
            'consejos' => $consejos,
            'video' => $video,
            'popupNews' => $popupNews,
            'googleMapsApiKey' => config('services.google_maps.key'),
        ]);
    }
}
