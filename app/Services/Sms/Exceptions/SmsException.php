<?php

namespace App\Services\Sms\Exceptions;

use RuntimeException;

class SmsException extends RuntimeException
{
    public static function sendFailed(string $provider, string $reason): self
    {
        return new self("[{$provider}] No se pudo enviar el SMS: {$reason}");
    }
}
