<?php

namespace App\Providers;

use App\Services\Cobranzas\CobranzasGatewayClient;
use Illuminate\Support\ServiceProvider;

class CobranzasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CobranzasGatewayClient::class, function () {
            return new CobranzasGatewayClient(
                // config('services.cobranzas.gateway_base_url') ya trae un default -- pero
                // env() no lo aplica si la clave existe vacía en .env (mismo caso real que ya
                // se encontró antes con COBRANZAS_BASE_URL: tira ConnectionException genérica
                // en vez de un error prolijo), así que se refuerza acá con `?:`.
                baseUrl: rtrim(config('services.cobranzas.gateway_base_url') ?: 'http://127.0.0.1:8001', '/'),
                apiKey: (string) config('services.cobranzas.gateway_api_key'),
            );
        });
    }
}
