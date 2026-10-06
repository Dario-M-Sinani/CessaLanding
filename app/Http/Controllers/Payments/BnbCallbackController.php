<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Recibo;
use App\Services\Cobranzas\FacturacionRecibo;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentProviderRegistry;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Endpoint que el BNB llama para notificar que un QR fue PAGADO (ver "Notificación de pagos QR"
 * en el PDF de Api Market). El BNB manda su propio formato -- {QRId, Gloss, sourceBankId,
 * originName, VoucherId, TransactionDateTime, additionalData} -- y espera una respuesta
 * {"success": true, "message": "OK"}.
 *
 * A diferencia del callback de SIP, este endpoint NO tiene Basic Auth (el BNB no lo contempla en
 * su notificación). Para que un aviso falso no pueda marcar un recibo como pagado, el pago se
 * confirma contra el propio BNB (getQRStatusAsync) antes de tocar el Recibo -- la notificación
 * solo dispara la verificación y aporta los datos del pagador.
 *
 * Reconciliación BNB -> Recibo: el BNB no conoce nuestro alias, así que se busca primero por
 * `additionalData` (donde BnbQrProvider mandó el alias al generar) y, si no, por el QRId contra
 * Recibo::provider_qr_id.
 */
class BnbCallbackController extends Controller
{
    public function receiveNotification(Request $request, PaymentProviderRegistry $providers): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'QRId' => ['required'],
            'Gloss' => ['nullable'],
            'sourceBankId' => ['nullable'],
            'originName' => ['nullable', 'string', 'max:250'],
            'VoucherId' => ['nullable', 'string', 'max:50'],
            'TransactionDateTime' => ['nullable', 'string', 'max:50'],
            'additionalData' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->respuesta(false, 'Datos inválidos: '.$validator->errors()->first());
        }

        $data = $validator->validated();
        $qrId = (string) $data['QRId'];
        $alias = $data['additionalData'] ?? null;

        $recibo = Recibo::query()
            ->where('provider', 'bnb')
            ->when(filled($alias), fn ($q) => $q->where('alias', $alias))
            ->when(blank($alias), fn ($q) => $q->where('provider_qr_id', $qrId))
            ->first();

        // Si vino alias pero no matcheó (p.ej. additionalData manipulado), reintentar por QRId.
        if (! $recibo && filled($alias)) {
            $recibo = Recibo::where('provider', 'bnb')->where('provider_qr_id', $qrId)->first();
        }

        if (! $recibo) {
            Log::warning('bnb_callback.qr_desconocido', ['qr_id' => $qrId, 'additional_data' => $alias]);

            return $this->respuesta(false, 'QR no reconocido');
        }

        // Idempotencia: si el dinero ya quedó registrado (Pagado/Facturado/ErrorFacturacion), no
        // reprocesar ni retroceder (el BNB reintenta la notificación). Un QR sólo Expirado/
        // Inhabilitado localmente todavía se puede confirmar: si el cliente pagó justo al filo,
        // el dinero entró y hay que honrarlo (se verifica igual contra el BNB abajo).
        if ($recibo->status->dineroYaRegistrado()) {
            Log::info('bnb_callback.ya_procesado', ['alias' => $recibo->alias, 'estado' => $recibo->status->value]);

            return $this->respuesta(true, 'OK');
        }

        // Verificación autoritativa: preguntarle al BNB si el QR está realmente Usado/Pagado.
        // Nunca se confía solo en la notificación (endpoint sin auth).
        try {
            $estado = $providers->get('bnb')->status($recibo->alias);
        } catch (QrPaymentException $e) {
            report($e);

            // No se pudo confirmar ahora: no se marca Pagado. El BNB reinting la notificación,
            // y de todos modos el estado se puede sincronizar a mano desde el panel.
            return $this->respuesta(false, 'No se pudo verificar el pago con el BNB en este momento');
        }

        if ($estado->status !== PaymentStatus::Pagado) {
            Log::warning('bnb_callback.pago_no_confirmado', [
                'alias' => $recibo->alias,
                'estado_bnb' => $estado->status->value,
            ]);

            return $this->respuesta(false, 'El BNB no reporta el QR como pagado');
        }

        $recibo->update([
            'status' => PaymentStatus::Pagado,
            'paid_at' => now(),
            // El BNB usa VoucherId como código de bancarización; es lo más cercano a un nº de orden.
            'provider_order_number' => $data['VoucherId'] ?? $estado->providerOrderNumber ?? $recibo->provider_order_number,
            'payer_name' => $data['originName'] ?? $recibo->payer_name,
            'callback_payload' => $request->all(),
        ]);

        Log::info('bnb_callback.pago_confirmado', ['alias' => $recibo->alias, 'recibo_id' => $recibo->id]);

        FacturacionRecibo::facturarTrasRespuesta($recibo);

        return $this->respuesta(true, 'OK');
    }

    private function respuesta(bool $success, string $message): JsonResponse
    {
        // El BNB siempre espera {success, message}; en caso de error, el motivo va en message.
        return response()->json(['success' => $success, 'message' => $message], 200);
    }
}
