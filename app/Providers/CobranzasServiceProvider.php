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
                // env() no lo aplica si la clave existe vacía en .env (caso real encontrado
                // probando la integración vieja: una URL vacía tira ConnectionException
                // genérica en vez de un error prolijo), así que se refuerza acá con `?:`.
                baseUrl: rtrim(config('services.cobranzas.gateway_base_url') ?: 'http://10.1.1.88:8000', '/'),
                apiKey: (string) config('services.cobranzas.gateway_api_key'),
            );
        });
    }
}
