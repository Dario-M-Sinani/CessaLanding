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
    // comercial de CESSA (api-cobranzas-bancos/SIIC, ver CobranzasGatewayClient) -- recién acá
    // existe un comprobante con valor fiscal descargable/imprimible.
    case Facturado = 'facturado';
    // El dinero ya se cobró pero registrar la factura contra api-cobranzas-bancos falló (después
    // de agotar los reintentos automáticos, ver RegistrarFacturacionCobranzas) -- nunca se pierde
    // este caso, requiere revisión manual desde el panel (ver ReciboResource).
    case ErrorFacturacion = 'error_facturacion';

    /**
     * Estados en los que el dinero del cliente ya quedó registrado como recibido (pagado, o
     * además facturado / con error de facturación). Un callback de pago que llega para un Recibo
     * en uno de estos estados NO debe reprocesarlo -- los bancos reintentan la notificación, y
     * volver a marcarlo "Pagado" haría que el cron lo facture de nuevo (doble facturación) o que
     * un Recibo ya Facturado retroceda. Los demás estados (Pendiente/Expirado/Inhabilitado/Error)
     * sí se pueden marcar Pagado cuando el banco confirma un pago real (aunque el QR ya haya
     * vencido localmente, el dinero entró y hay que honrarlo).
     */
    public function dineroYaRegistrado(): bool
    {
        return in_array($this, [self::Pagado, self::Facturado, self::ErrorFacturacion], true);
    }

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
