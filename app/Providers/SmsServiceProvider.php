<?php

namespace App\Providers;

use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Providers\LogSmsProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Arma el proveedor de SMS disponible. Hoy solo hay un stub que escribe al log (todavía no
 * hay documentación de la API de Tigo, el operador con el que CESSA ya tiene contrato de
 * servicio de SMS) -- cuando llegue, se agrega la clase real (ej. TigoSmsProvider) y se
 * cambia el binding acá, sin tocar nada de lo que la consume (ActualizarDatosController).
 */
class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProviderInterface::class, function () {
            return match (config('services.sms.provider', 'log')) {
                default => new LogSmsProvider(),
            };
        });
    }
}
