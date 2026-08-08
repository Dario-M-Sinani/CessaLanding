<?php

namespace App\Services\Payments;

/**
 * Estado normalizado de un cobro por QR, independiente del banco/proveedor que lo procese.
 */
enum PaymentStatus: string
{
    case Pendiente = 'pendiente';
    case Pagado = 'pagado';
    case Inhabilitado = 'inhabilitado';
    case Expirado = 'expirado';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Pagado => 'Pagado',
            self::Inhabilitado => 'Inhabilitado',
            self::Expirado => 'Expirado',
            self::Error => 'Error',
        };
    }
}
