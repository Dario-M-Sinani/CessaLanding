<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GaleriaController extends Controller
{
    public function index(): Response
    {
        $videos = Video::where('published', 'S')
            ->orderBy('position', 'asc')
            ->get();

        return Inertia::render('Galeria/Index', [
            'videos' => $videos,
        ]);
    }

    public function imagenes(): Response
    {
        $images = Image::where('published', 'S')
            ->where('category', 'galeria')
            ->orderBy('position', 'asc')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Galeria/Imagenes', [
            'images' => $images,
        ]);
    }

    public function trabajadores(): Response
    {
        $images = Image::where('published', 'S')
            ->where('category', 'trabajadores')
            ->orderBy('position', 'asc')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Galeria/Trabajadores', [
            'images' => $images,
        ]);
    }
}
