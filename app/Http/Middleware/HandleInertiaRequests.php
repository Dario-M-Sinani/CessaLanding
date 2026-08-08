<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Menú "Consumidor" del Navbar: se arma solo, en vivo, desde lo publicado en esa
            // categoría -- antes eran 7 links escritos a mano en Navbar.vue, así que un Content
            // nuevo (o uno que se despublicaba) nunca cambiaba el menú sin tocar código.
            // "Consejos de seguridad" (alias consejos-de-seguridad) se excluye a propósito: ese
            // Content sigue en la BD para no perder el texto legacy, pero la página real que se
            // muestra al público es una rediseñada a mano en /informacion/consejos-de-seguridad,
            // no /contenido/{alias} -- Navbar.vue agrega ese link aparte, fijo.
            'consumidorLinks' => fn () => \App\Models\Content::query()
                ->whereHas('category', fn ($query) => $query->where('title', 'Consumidor'))
                ->where('published', 'S')
                ->where('alias', '!=', 'consejos-de-seguridad')
                ->orderBy('id')
                ->get(['title', 'alias'])
                ->map(fn ($content) => [
                    'label' => $content->title,
                    'href' => "/contenido/{$content->alias}",
                ])
                ->values(),
        ];
    }
}
