<?php

namespace App\Providers;

use App\Services\Cobranzas\CobranzasBancoService;
use Illuminate\Support\ServiceProvider;

class CobranzasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CobranzasBancoService::class, function () {
            return new CobranzasBancoService(
                // config('services.cobranzas.base_url') ya trae un default -- pero env() no lo
                // aplica si la clave existe vacía en .env (caso real encontrado probando esto:
                // "COBRANZAS_BASE_URL=" sin valor tira ConnectionException genérica en vez de un
                // error prolijo), así que se refuerza acá con `?:`.
                baseUrl: rtrim(config('services.cobranzas.base_url') ?: 'http://localhost:6001', '/'),
                clientId: (string) config('services.cobranzas.client_id'),
                clientSecret: (string) config('services.cobranzas.client_secret'),
                username: (string) config('services.cobranzas.username'),
                password: (string) config('services.cobranzas.password'),
                agenciaSigla: (string) config('services.cobranzas.agencia_sigla'),
            );
        });
    }
}
