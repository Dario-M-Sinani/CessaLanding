<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\DataTransferObjects\QrPaymentResult;
use App\Services\Payments\DataTransferObjects\QrPaymentStatusResult;
use App\Services\Payments\Exceptions\QrPaymentException;

/**
 * Contrato que debe cumplir cualquier pasarela de QR de pago (SIP/BISA hoy, otros bancos
 * después) para poder generar/consultar/inhabilitar un cobro sin que el resto de la app sepa
 * qué banco hay detrás. Un `provider_key` nuevo + una clase que implemente esta interfaz +
 * una entrada en el binding (ver PaymentServiceProvider) alcanza para sumar un banco más.
 */
interface QrPaymentProviderInterface
{
    /**
     * Identificador corto y estable del proveedor (ej. "sip_bisa") -- se guarda en
     * Recibo::provider para saber con qué integración se generó cada cobro.
     */
    public function key(): string;

    /**
     * @throws QrPaymentException
     */
    public function generate(QrPaymentRequest $request): QrPaymentResult;

    /**
     * @throws QrPaymentException
     */
    public function disable(string $alias): void;

    /**
     * @throws QrPaymentException
     */
    public function status(string $alias): QrPaymentStatusResult;
}
