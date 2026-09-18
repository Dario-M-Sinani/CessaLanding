<?php

namespace App\Services\Cobranzas\Exceptions;

use RuntimeException;

/**
 * Error al hablar con el gateway propio (cobranza-cessa) que a su vez factura contra
 * api-cobranzas-bancos -- mismo criterio que QrPaymentException (ver esa clase), un solo tipo
 * de excepción para que el código que llama a CobranzasGatewayClient no tenga que conocer
 * detalles internos.
 */
class CobranzasException extends RuntimeException
{
    public static function requestFailed(string $operation, string $reason): self
    {
        return new self("Falló \"{$operation}\" contra el gateway de cobranzas (cobranza-cessa): {$reason}");
    }
}
