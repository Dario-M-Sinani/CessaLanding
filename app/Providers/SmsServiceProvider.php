<?php

namespace App\Providers;

use App\Models\DemoConfiguracion;
use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Providers\LogSmsProvider;
use App\Services\Sms\Providers\TigoSmsProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Arma el proveedor de SMS disponible según SMS_PROVIDER en .env ('log' por defecto, 'tigo'
 * para la API real de Tigo Business -- ver TigoSmsProvider y config('services.sms.tigo')).
 */
class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProviderInterface::class, function () {
            return match (config('services.sms.provider', 'log')) {
                'tigo' => new TigoSmsProvider(
                    baseUrl: rtrim(config('services.sms.tigo.base_url'), '/'),
                    sender: config('services.sms.tigo.sender'),
                    // El token en DemoConfiguracion (SQLite aparte) tiene prioridad sobre el
                    // TIGO_SMS_TOKEN de .env -- se puede actualizar desde la página oculta de
                    // administración y surte efecto en el siguiente envío, sin reiniciar el
                    // servidor (cambiar el .env sí lo requiere, ver ESTADO_SEGURIDAD_MIGRACION.md §3.25).
                    staticToken: DemoConfiguracion::obtener('tigo_sms_token') ?: config('services.sms.tigo.token'),
                    clientId: config('services.sms.tigo.client_id'),
                    clientSecret: config('services.sms.tigo.client_secret'),
                ),
                default => new LogSmsProvider(),
            };
        });
    }
}
