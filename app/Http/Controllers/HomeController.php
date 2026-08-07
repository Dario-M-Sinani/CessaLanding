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
        $outages = ScheduledOutage::where('published', 'S')
            ->whereDate('execution_date', '>=', now()->toDateString())
            ->orderBy('execution_date')
            ->limit(3)
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

        $video = Video::where('published', 'S')->orderBy('position')->first();

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
