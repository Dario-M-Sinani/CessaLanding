<?php

namespace App\Services\Cobranzas\Exceptions;

use RuntimeException;

/**
 * Error al hablar con api-cobranzas-bancos (auth, caja, transacción, comprobante, red caída,
 * etc.) -- mismo criterio que QrPaymentException (ver esa clase), un solo tipo de excepción
 * para que el código que llama a CobranzasBancoService no tenga que conocer detalles internos.
 */
class CobranzasException extends RuntimeException
{
    public static function authenticationFailed(string $reason): self
    {
        return new self("No se pudo autenticar contra api-cobranzas-bancos: {$reason}");
    }

    public static function requestFailed(string $operation, string $reason): self
    {
        return new self("Falló \"{$operation}\" en api-cobranzas-bancos: {$reason}");
    }
}
