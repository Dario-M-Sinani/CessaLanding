<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Image;
use App\Models\News;
use App\Models\Publication;
use App\Models\ScheduledOutage;
use App\Models\Video;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $outages = ScheduledOutage::programados()
            ->where('published', 'S')
            ->whereDate('execution_date', '>=', now()->toDateString())
            ->orderBy('execution_date')
            ->limit(3)
            ->get();

        $emergencies = ScheduledOutage::emergenciasVisibles()
            ->orderByRaw('restored_at IS NOT NULL')
            ->orderByDesc('execution_date')
            ->orderByDesc('start_time')
            ->get();

        $documentGroupCounts = Publication::countsByGroup();
        $documentGroups = collect(Publication::getGroupLabels())
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => $documentGroupCounts[$key] ?? 0,
            ])
            ->values();

        $consejos = Content::where('alias', 'consejos-de-seguridad')
            ->where('published', 'S')
            ->first();

        // El bloque "Video Consejo" del home solo sabe embeber YouTube: si el primer video
        // publicado es de Facebook/Instagram/TikTok (pensados para la galería), el bloque
        // quedaba vacío. Se toma el primer video publicado que sea de YouTube.
        $video = Video::where('published', 'S')
            ->where(fn ($q) => $q->where('url', 'like', '%youtube.com/%')->orWhere('url', 'like', '%youtu.be/%'))
            ->orderBy('position')
            ->first();

        $popupNews = News::where('published', 'S')
            ->where('popup', true)
            ->orderBy('created_at', 'desc')
            ->first();

        $galleryHighlights = Image::where('published', 'S')
            ->where('home_carousel', true)
            ->orderBy('position')
            ->get(['id', 'title', 'url']);

        $galleryHighlightsMobile = Image::where('published', 'S')
            ->where('home_carousel_mobile', true)
            ->orderBy('position')
            ->get(['id', 'title', 'url']);

        return Inertia::render('Home', [
            'outages' => $outages,
            'emergencies' => $emergencies,
            'documentGroups' => $documentGroups,
            'consejos' => $consejos,
            'video' => $video,
            'popupNews' => $popupNews,
            'galleryHighlights' => $galleryHighlights,
            'galleryHighlightsMobile' => $galleryHighlightsMobile,
            'googleMapsApiKey' => config('services.google_maps.key'),
        ]);
    }
}
