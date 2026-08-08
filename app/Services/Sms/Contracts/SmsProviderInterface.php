<?php

namespace App\Services\Sms\Contracts;

use App\Services\Sms\Exceptions\SmsException;

/**
 * Contrato para el envío de SMS, mismo espíritu que QrPaymentProviderInterface: hoy solo hay
 * un stub que escribe al log (CESSA todavía no tiene la documentación de la API de Tigo, con
 * quien ya existe contrato de servicio de SMS), pero el resto de la app solo depende de esta
 * interfaz -- cuando llegue la documentación, alcanza con sumar una clase que la implemente y
 * cambiar el binding en SmsServiceProvider.
 */
interface SmsProviderInterface
{
    /**
     * Identificador corto y estable del proveedor (ej. "log", "tigo").
     */
    public function key(): string;

    /**
     * @throws SmsException
     */
    public function send(string $phone, string $message): void;
}
