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

            // Mismo mecanismo que consumidorLinks, para "La Compañía": cualquier Content nuevo
            // que se publique en esa categoría aparece solo, al final del dropdown, sin tocar
            // código. A diferencia de Consumidor, "La Compañía" tiene además 5 páginas curadas
            // a mano con su propia ruta (LaCompaniaController, ver ESTADO_SEGURIDAD_MIGRACION.md
            // §3.20sexies) -- esos alias se excluyen acá para no duplicarlas con la versión
            // genérica en /contenido/{alias}. También se excluyen "personal-autorizado-cessa"
            // (reemplazada por el módulo Personal, §3.26 -- se dejó publicada en la BD a
            // propósito, sin link, no debe resucitar sola acá) y "tramites-derechos-y-requisitos"
            // (ya tiene su propio link fijo en el dropdown Información) y "contactenos" (la
            // página real de Contacto usa ContactInfo, no este Content).
            'companiaLinks' => fn () => \App\Models\Content::query()
                ->whereHas('category', fn ($query) => $query->where('title', 'La Compañía'))
                ->where('published', 'S')
                ->whereNotIn('alias', [
                    'quienes-somos', 'historia', 'mision-y-vision', 'estructura-organizacional',
                    'recursos-humanos', 'personal-autorizado-cessa',
                    'tramites-derechos-y-requisitos', 'contactenos',
                ])
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
