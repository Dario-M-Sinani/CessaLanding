<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Stub de desarrollo: en vez de llamar a una API real, deja constancia en el log de que el
 * SMS "se habría enviado". Sirve para poder probar el flujo completo de Actualizar Datos
 * (incluida la verificación del código) sin tener todavía la documentación de la API de Tigo.
 * Nunca usar en producción -- ver App\Providers\SmsServiceProvider.
 */
class LogSmsProvider implements SmsProviderInterface
{
    public function key(): string
    {
        return 'log';
    }

    public function send(string $phone, string $message): void
    {
        Log::info('sms.log_provider.enviado', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
