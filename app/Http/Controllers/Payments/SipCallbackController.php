<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Endpoint que SIP llama para confirmar que un QR fue pagado (ver §3.1 "CALL BACK" de la
 * especificación). SIP espera SIEMPRE una respuesta con "codigo" -- cualquier cosa que no sea
 * "0000" hace que le mande un correo de error a todos los administradores de la cuenta SIP, así
 * que ese código se usa a propósito como señal de "esto no cuadra, revisar" (ej. alias
 * desconocido) en vez de fallar en silencio.
 */
class SipCallbackController extends Controller
{
    public function confirmarPago(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'alias' => ['required', 'string', 'max:50'],
            'numeroOrdenOriginante' => ['nullable', 'string', 'max:30'],
            'monto' => ['nullable', 'numeric'],
            'idQr' => ['nullable', 'string', 'max:30'],
            'moneda' => ['nullable', 'string', 'max:10'],
            'cuentaCliente' => ['nullable', 'string', 'max:50'],
            'nombreCliente' => ['nullable', 'string', 'max:250'],
            'documentoCliente' => ['nullable', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'codigo' => '9998',
                'mensaje' => 'Datos inválidos: '.$validator->errors()->first(),
            ], 400);
        }

        $data = $validator->validated();

        $recibo = Recibo::where('alias', $data['alias'])->first();

        if (! $recibo) {
            Log::warning('sip_callback.alias_desconocido', ['alias' => $data['alias']]);

            return response()->json([
                'codigo' => '9999',
                'mensaje' => 'Alias no reconocido',
            ], 200);
        }

        $recibo->update([
            'status' => PaymentStatus::Pagado,
            'paid_at' => now(),
            'provider_order_number' => $data['numeroOrdenOriginante'] ?? $recibo->provider_order_number,
            'payer_account' => $data['cuentaCliente'] ?? $recibo->payer_account,
            'payer_name' => $data['nombreCliente'] ?? $recibo->payer_name,
            'payer_document' => $data['documentoCliente'] ?? $recibo->payer_document,
            'callback_payload' => $request->all(),
        ]);

        Log::info('sip_callback.pago_confirmado', ['alias' => $recibo->alias, 'recibo_id' => $recibo->id]);

        return response()->json([
            'codigo' => '0000',
            'mensaje' => 'Registro Exitoso',
        ]);
    }
}
