<?php

namespace App\Console\Commands;

use App\Models\Recibo;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentProviderRegistry;
use App\Services\Payments\PaymentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Respaldo del callback de SIP (SipCallbackController): los pagos BISA se confirman normalmente
 * por ese aviso, pero si no llega (Basic Auth mal registrado en el portal SIP, Cloudflare, red),
 * el cliente pagó y el recibo terminaría Expirado sin registrar la plata. Mismo mecanismo que
 * pagos:sincronizar-bnb: cada minuto se consulta estadoTransaccion de los QR SIP propios que
 * siguen Pendientes o que vencieron/se inhabilitaron hace poco, y los que SIP da por PAGADO se
 * marcan Pagado (de ahí sigue la facturación de siempre). Corre antes de pagos:expirar-vencidos.
 */
class SincronizarPagosSip extends Command
{
    // Ventana para re-consultar QR ya vencidos/inhabilitados localmente.
    private const VENTANA_MINUTOS = 30;

    protected $signature = 'pagos:sincronizar-sip';

    protected $description = 'Consulta a SIP el estado de los QR BISA pendientes o recién vencidos y marca Pagado los confirmados (respaldo del callback).';

    public function handle(PaymentProviderRegistry $providers): int
    {
        $recibos = Recibo::where('provider', 'sip_bisa')
            ->whereIn('status', [PaymentStatus::Pendiente, PaymentStatus::Expirado, PaymentStatus::Inhabilitado])
            ->whereNotNull('qr_image_path')
            ->where('created_at', '>=', now()->subMinutes(self::VENTANA_MINUTOS))
            ->get();

        if ($recibos->isEmpty()) {
            return self::SUCCESS;
        }

        $sip = $providers->get('sip_bisa');
        $pagados = 0;

        foreach ($recibos as $recibo) {
            try {
                $estado = $sip->status($recibo->alias);
            } catch (QrPaymentException $e) {
                report($e);

                continue;
            }

            if ($estado->status !== PaymentStatus::Pagado) {
                continue;
            }

            // Pudo haber llegado el callback mientras tanto: no pisar un Pagado/Facturado.
            $recibo->refresh();
            if ($recibo->status->dineroYaRegistrado()) {
                continue;
            }

            $recibo->update([
                'status' => PaymentStatus::Pagado,
                'paid_at' => $estado->processedAt ?? now(),
                'provider_order_number' => $estado->providerOrderNumber ?? $recibo->provider_order_number,
                'payer_account' => $estado->payerAccount ?? $recibo->payer_account,
                'payer_name' => $estado->payerName ?? $recibo->payer_name,
                'payer_document' => $estado->payerDocument ?? $recibo->payer_document,
            ]);
            $pagados++;

            Log::warning('sip_sincronizacion.pago_sin_callback', ['alias' => $recibo->alias, 'recibo_id' => $recibo->id]);
        }

        if ($pagados > 0) {
            $this->info("Pagos SIP confirmados sin callback: {$pagados}");
        }

        return self::SUCCESS;
    }
}
