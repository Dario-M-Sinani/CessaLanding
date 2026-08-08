<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Solo en local: url()/asset()/Storage::url() usan el host+esquema de la request
        // actual en vez del valor fijo de APP_URL. En local se accede indistintamente por
        // localhost:8000, 127.0.0.1:8000 o una IP de LAN -- si APP_URL queda fijo en uno de
        // esos, cualquier fetch() propio del navegador hacia una URL absoluta con el OTRO host
        // (ej. FilePond pidiendo la miniatura de una imagen elegida del Gestor de Archivos) lo
        // trata como cross-origin y lo bloquea por CORS, aunque sea el mismo server. En
        // producción esto NO se activa a propósito -- ahí APP_URL debe seguir siendo el dominio
        // real fijo (ver checklist de despliegue en ESTADO_SEGURIDAD_MIGRACION.md).
        if ($this->app->environment('local') && ! $this->app->runningInConsole()) {
            $rootUrl = request()->getSchemeAndHttpHost();

            URL::forceRootUrl($rootUrl);

            // El disco 'public' arma su URL leyendo APP_URL directo en config/filesystems.php
            // (no pasa por el helper url()/URL facade), así que forceRootUrl() por sí solo no
            // alcanza para Storage::url() -- hay que pisar esa config puntual también.
            config(['filesystems.disks.public.url' => $rootUrl.'/storage']);
        }
    }
}
