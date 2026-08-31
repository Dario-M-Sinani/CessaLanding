<?php

namespace App\Console\Commands;

use App\Models\Recibo;
use App\Services\Cobranzas\FacturacionRecibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Console\Command;

/**
 * Registra en api-cobranzas-bancos (SIIC) los Recibos que ya se cobraron por QR pero todavía
 * no quedaron como factura real -- corre cada minuto (ver routes/console.php), mismo patrón que
 * ExpirarRecibosVencidos (poll-based en vez de una cola, porque el hosting compartido no tiene
 * un daemon de queue:work corriendo).
 *
 * Apagado por defecto (services.cobranzas.enabled) hasta probar la integración de forma aislada
 * contra api-cobranzas-bancos -- ver documentacion/PLAN_PAGO_Y_FACTURACION.md. Mientras esté
 * apagado, este comando no hace nada (los Recibos quedan en "Pagado" hasta que se prenda).
 */
class RegistrarFacturacionCobranzas extends Command
{
    protected $signature = 'pagos:registrar-facturacion';

    protected $description = 'Registra en api-cobranzas-bancos los cobros por QR ya pagados y descarga el comprobante.';

    public function handle(FacturacionRecibo $facturacion): int
    {
        if (! config('services.cobranzas.enabled')) {
            return self::SUCCESS;
        }

        $pendientes = Recibo::where('status', PaymentStatus::Pagado)
            ->whereNotNull('debt_items')
            ->orderBy('paid_at')
            ->limit(20)
            ->get();

        // Los que ya agotaron los reintentos automáticos (ErrorFacturacion + intentos >= tope)
        // no se vuelven a tocar acá -- necesitan revisión manual, ver ReciboResource
        // ("Reintentar Facturación").
        $reintentables = Recibo::where('status', PaymentStatus::ErrorFacturacion)
            ->where('facturacion_intentos', '<', FacturacionRecibo::MAX_INTENTOS_AUTOMATICOS)
            ->orderBy('paid_at')
            ->limit(20)
            ->get();

        $recibos = $pendientes->concat($reintentables);

        foreach ($recibos as $recibo) {
            $facturacion->procesar($recibo->fresh());
        }

        if ($recibos->isNotEmpty()) {
            $this->info("Recibos procesados: {$recibos->count()}");
        }

        return self::SUCCESS;
    }
}
