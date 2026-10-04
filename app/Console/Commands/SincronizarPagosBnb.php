<?php

namespace App\Console\Commands;

use App\Models\Recibo;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentProviderRegistry;
use App\Services\Payments\PaymentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * La cuenta BNB de CESSA es compartida con otro sistema de producción (~600 QR/día) y el BNB
 * solo admite UNA URL de notificación por cuenta, que hoy apunta a ese sistema
 * (cessa-push.cloudfunctions.net). Apuntarla a la web lo rompería, así que la web no recibe el
 * aviso de pago del BNB: en su lugar, cada minuto se consulta getQRStatusAsync por los QR BNB
 * propios que siguen Pendientes o que vencieron/se inhabilitaron hace poco (un pago justo al
 * filo puede entrar antes de que el BNB procese la cancelación). Si el BNB lo da por pagado, se
 * marca Pagado y el cron de facturación sigue igual que con el callback (BnbCallbackController).
 */
class SincronizarPagosBnb extends Command
{
    // Ventana para re-consultar QR ya vencidos/inhabilitados localmente.
    private const VENTANA_MINUTOS = 30;

    protected $signature = 'pagos:sincronizar-bnb';

    protected $description = 'Consulta al BNB el estado de los QR BNB pendientes o recién vencidos y marca Pagado los confirmados.';

    public function handle(PaymentProviderRegistry $providers): int
    {
        $recibos = Recibo::where('provider', 'bnb')
            ->whereIn('status', [PaymentStatus::Pendiente, PaymentStatus::Expirado, PaymentStatus::Inhabilitado])
            ->whereNotNull('provider_qr_id')
            ->where('created_at', '>=', now()->subMinutes(self::VENTANA_MINUTOS))
            ->get();

        $bnb = $providers->get('bnb');
        $pagados = 0;

        foreach ($recibos as $recibo) {
            try {
                $estado = $bnb->status($recibo->alias);
            } catch (QrPaymentException $e) {
                report($e);

                continue;
            }

            if ($estado->status !== PaymentStatus::Pagado) {
                continue;
            }

            $recibo->update([
                'status' => PaymentStatus::Pagado,
                'paid_at' => now(),
                'provider_order_number' => $estado->providerOrderNumber ?? $recibo->provider_order_number,
            ]);
            $pagados++;

            Log::info('bnb_sincronizacion.pago_confirmado', ['alias' => $recibo->alias, 'recibo_id' => $recibo->id]);
        }

        if ($pagados > 0) {
            $this->info("Pagos BNB confirmados: {$pagados}");
        }

        return self::SUCCESS;
    }
}
