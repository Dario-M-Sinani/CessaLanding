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
    // El dinero ya se cobró (Pagado) y además quedó registrado como factura real en el sistema
    // comercial de CESSA (api-cobranzas-bancos/SIIC, ver CobranzasBancoService) -- recién acá
    // existe un comprobante con valor fiscal descargable/imprimible.
    case Facturado = 'facturado';
    // El dinero ya se cobró pero registrar la factura contra api-cobranzas-bancos falló (después
    // de agotar los reintentos automáticos, ver RegistrarFacturacionCobranzas) -- nunca se pierde
    // este caso, requiere revisión manual desde el panel (ver ReciboResource).
    case ErrorFacturacion = 'error_facturacion';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Pagado => 'Pagado',
            self::Inhabilitado => 'Inhabilitado',
            self::Expirado => 'Expirado',
            self::Error => 'Error',
            self::Facturado => 'Facturado',
            self::ErrorFacturacion => 'Pagado, error al facturar',
        };
    }
}
