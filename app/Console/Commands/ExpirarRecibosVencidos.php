<?php

namespace App\Console\Commands;

use App\Models\Recibo;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Illuminate\Console\Command;

/**
 * SIP redondea "fechaVencimiento" al fin del día (ver SipQrProvider::generate -- no acepta
 * precisión de minutos), pero los QR que dispara el cliente desde Consulta de Deuda
 * (PagoQrController) deben vencer a los 5 minutos igual. Ese límite se hace cumplir acá:
 * corre cada minuto (ver routes/console.php) e inhabilita en el proveedor + marca localmente
 * Expirado cualquier recibo Pendiente cuyo expires_at propio ya pasó.
 */
class ExpirarRecibosVencidos extends Command
{
    protected $signature = 'pagos:expirar-vencidos';

    protected $description = 'Inhabilita en el proveedor y marca como Expirado los recibos QR pendientes que ya vencieron.';

    public function handle(QrPaymentProviderInterface $provider): int
    {
        $vencidos = Recibo::where('status', PaymentStatus::Pendiente)
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($vencidos as $recibo) {
            try {
                $provider->disable($recibo->alias);
            } catch (QrPaymentException $e) {
                report($e);
            }

            $recibo->update(['status' => PaymentStatus::Expirado]);
        }

        if ($vencidos->isNotEmpty()) {
            $this->info("Recibos expirados: {$vencidos->count()}");
        }

        return self::SUCCESS;
    }
}
