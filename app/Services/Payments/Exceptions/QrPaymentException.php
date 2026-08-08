<?php

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Error al hablar con la pasarela de pagos (auth, respuesta de error, red caída, etc.) --
 * un solo tipo de excepción sin importar qué banco/proveedor esté detrás, para que el código
 * que llama a QrPaymentProviderInterface no tenga que conocer detalles de cada integración.
 */
class QrPaymentException extends RuntimeException
{
    public static function authenticationFailed(string $provider, string $reason): self
    {
        return new self("[{$provider}] No se pudo autenticar: {$reason}");
    }

    public static function requestFailed(string $provider, string $operation, string $reason): self
    {
        return new self("[{$provider}] Falló \"{$operation}\": {$reason}");
    }
}
