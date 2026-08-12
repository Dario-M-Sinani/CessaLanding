<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Services\CessaApiService;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pago por QR propio (vía SIP/BISA, ver QrPaymentProviderInterface) disparado por el cliente
 * mismo desde Consulta de Deuda -- hasta ahora este cobro solo lo podía generar el staff desde
 * el panel (ReciboResource/ListRecibos). Este controller calca esa misma lógica de generación,
 * pero verificando la cuenta contra SIIC él mismo (nunca confía en el monto que mande el
 * cliente) igual que ConsultaDeudaController.
 */
class PagoQrController extends Controller
{
    // Tope de negocio propio de CESSA (no es un límite de SIP -- no hay uno documentado, ver
    // ESTADO_SEGURIDAD_MIGRACION.md). Protege contra montos anómalos del sistema comercial
    // SIIC que un cliente real nunca debería terminar pagando por QR de una sola vez.
    private const LIMITE_MONTO_QR = 50000.0;

    protected CessaApiService $apiService;

    public function __construct(CessaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function generar(Request $request): JsonResponse
    {
        // El sistema de cobros hace su corte diario entre 23:59 y 00:00 (hora de Bolivia,
        // no la del servidor -- ver config/app.php, corre en UTC); generar un QR justo en
        // ese minuto no es confiable, así que se bloquea acá antes de gastar una llamada a
        // SIIC/SIP. El frontend ya deshabilita el botón en ese mismo horario (ver
        // ConsultaDeuda.vue), esto es la validación autoritativa.
        $ahoraBolivia = Carbon::now('America/La_Paz');
        if ($ahoraBolivia->hour === 23 && $ahoraBolivia->minute === 59) {
            return response()->json(['message' => 'El pago por QR no está disponible entre las 23:59 y las 00:00 por el corte diario del sistema. Intenta nuevamente en unos minutos.'], 422);
        }

        $validated = $request->validate([
            'nro_cliente' => ['required', 'digits_between:1,10'],
            'zona' => ['required', 'digits_between:1,3'],
            'manzano' => ['required', 'digits_between:1,4'],
            'correlativo' => ['required', 'digits_between:1,6'],
            // Cuántos de los avisos pendientes (empezando siempre por el más antiguo) se
            // pagan con este QR. Si no se manda, se paga todo (comportamiento de siempre).
            'cantidad_meses' => ['nullable', 'integer', 'min:1'],
        ]);

        // Mismo doble factor y misma verificación contra SIIC que ConsultaDeudaController::consultar()
        // (duplicado a propósito acá, no refactorizado, para no tocar ese controller ya auditado).
        try {
            $data = $this->apiService->consultaDeuda([
                'nro_cliente' => $validated['nro_cliente'],
                'ver_deuda' => 'si',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se pudo conectar con el sistema comercial SIIC. Intenta más tarde.'], 502);
        }

        if (isset($data['error']) || empty($data['nro_cliente'])) {
            return response()->json(['message' => 'No se encontró ningún abonado con ese número.'], 422);
        }

        if (
            (int) ($data['zona'] ?? -1) !== (int) $validated['zona']
            || (int) ($data['manzano'] ?? -1) !== (int) $validated['manzano']
            || (int) ($data['correlativo'] ?? -1) !== (int) $validated['correlativo']
        ) {
            Log::warning('pago_qr.cuenta_no_coincide', [
                'ip' => $request->ip(),
                'nro_cliente' => $validated['nro_cliente'],
            ]);

            return response()->json(['message' => 'Los datos ingresados no coinciden con ningún abonado registrado.'], 422);
        }

        // SIIC no permite pagar avisos recientes sin considerar los más antiguos primero
        // (misma regla que aplica la API de cobranzas al registrar el pago), así que acá
        // se ordena el detalle cronológicamente y solo se admite pagar los N más viejos --
        // nunca "solo el último mes" salteando uno anterior sin pagar.
        // No se confía en que SIIC ya mande `importe` con el signo correcto para las
        // conciliaciones -- se trata como magnitud y el signo se deriva de `debito_credito`
        // (mismo criterio que ConsultaDeudaController, ver ese archivo para el porqué).
        $pendientes = collect($data['deuda'] ?? [])
            ->sortBy([
                fn ($a, $b) => ((int) $a['anio']) <=> ((int) $b['anio']),
                fn ($a, $b) => ((int) $a['mes']) <=> ((int) $b['mes']),
            ])
            ->values()
            ->map(function (array $item) {
                $magnitud = abs((float) ($item['importe'] ?? 0));
                $item['importe_firmado'] = ($item['debito_credito'] ?? 'DEBITO') === 'CREDITO' ? -$magnitud : $magnitud;

                return $item;
            });

        if ($pendientes->isEmpty()) {
            return response()->json(['message' => 'Esta cuenta no registra deuda pendiente para pagar.'], 422);
        }

        $cantidadMeses = min($validated['cantidad_meses'] ?? $pendientes->count(), $pendientes->count());
        $aPagar = $pendientes->take($cantidadMeses);
        $monto = round((float) $aPagar->sum(fn ($item) => (float) $item['importe_firmado']), 2);

        // La selección puede incluir conciliaciones (créditos) que dejan el subtotal en 0 o
        // negativo si no se llega a incluir suficientes facturas más nuevas -- no se puede
        // cobrar eso por QR. El frontend ya guía al cliente para que no llegue a este punto
        // (ver ConsultaDeuda.vue), esto es la validación autoritativa.
        if ($monto <= 0) {
            return response()->json(['message' => 'La selección de meses no tiene un monto positivo para cobrar. Elegí hasta un mes donde el total vuelva a ser positivo.'], 422);
        }

        // Caso real detectado: una cuenta con una deuda de SIIC de más de Bs. 6.000.000 (dato
        // anómalo del sistema comercial -- no algo que corresponda cobrarle a un cliente real
        // por QR). El frontend ya avisa antes de llegar acá (ver ConsultaDeuda.vue), esto es
        // la validación autoritativa.
        if ($monto > self::LIMITE_MONTO_QR) {
            return response()->json(['message' => 'Este monto supera el límite permitido para pago por QR (Bs. '.number_format(self::LIMITE_MONTO_QR, 0, '.', '.').'). No se puede realizar esta transacción por este medio.'], 422);
        }

        $MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $primero = $aPagar->first();
        $ultimo = $aPagar->last();

        // Legible, para mostrarle al cliente en el modal de pago (sin límite de caracteres).
        $periodo = $primero === $ultimo
            ? "{$MESES[(int) $primero['mes']]}/{$primero['anio']}"
            : "{$MESES[(int) $primero['mes']]}-{$MESES[(int) $ultimo['mes']]}/{$ultimo['anio']}";

        // Glosa que viaja al QR/banco: SIP la corta a 30 caracteres, así que va compacta
        // (mes y año en número, no en texto) pero con lo que el cliente necesita para
        // identificar el pago en su comprobante: importe, cuántos meses paga, qué periodo
        // y su número de cliente -- este último nunca se debe truncar (es la clave de
        // conciliación), así que si agregar "cuántos meses" no entra en 30 caracteres se
        // omite ese dato antes de arriesgarse a cortar el resto.
        $mesInicioNum = str_pad((string) $primero['mes'], 2, '0', STR_PAD_LEFT);
        $mesFinNum = str_pad((string) $ultimo['mes'], 2, '0', STR_PAD_LEFT);
        $periodoNumerico = $primero === $ultimo
            ? "{$mesInicioNum}/{$primero['anio']}"
            : "{$mesInicioNum}-{$mesFinNum}/{$ultimo['anio']}";
        $montoTexto = floor($monto) == $monto
            ? number_format($monto, 0, '.', '')
            : number_format($monto, 2, '.', '');
        $glosaBase = "{$montoTexto}Bs {$periodoNumerico} C{$validated['nro_cliente']}";
        $glosaConCantidad = "{$montoTexto}Bs {$cantidadMeses}m {$periodoNumerico} C{$validated['nro_cliente']}";
        $glosa = mb_strlen($glosaConCantidad) <= 30 ? $glosaConCantidad : Str::limit($glosaBase, 30, '');

        $provider = app(QrPaymentProviderInterface::class);
        $alias = 'CESSA-WEB-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        $expiresAt = now()->addMinutes(5);

        // Evita que un mismo cliente termine con más de un QR válido a la vez (p.ej. si
        // vuelve a tocar "Pagar con QR" con uno anterior todavía sin pagar): se inhabilita
        // en SIP y localmente cualquier QR pendiente previo de este nro_cliente antes de
        // generar el nuevo. `lockForUpdate` evita que dos requests casi simultáneas del
        // mismo cliente dejen dos recibos "pendiente" en pie a la vez.
        DB::transaction(function () use ($validated, $provider) {
            $anteriores = Recibo::where('nro_cliente', $validated['nro_cliente'])
                ->where('status', PaymentStatus::Pendiente)
                ->lockForUpdate()
                ->get();

            foreach ($anteriores as $anterior) {
                try {
                    $provider->disable($anterior->alias);
                } catch (QrPaymentException $e) {
                    // Si SIP ya lo dio de baja solo (p.ej. venció) esto puede fallar; no debe
                    // bloquear la generación del nuevo QR, solo se registra para revisar.
                    report($e);
                }

                $anterior->update(['status' => PaymentStatus::Inhabilitado]);
            }
        });

        try {
            $result = $provider->generate(new QrPaymentRequest(
                alias: $alias,
                amount: $monto,
                currency: 'BOB',
                description: $glosa,
                // SIP redondea "fechaVencimiento" al fin del día (ver SipQrProvider::generate,
                // no acepta precisión de minutos), así que el vencimiento real a 5 minutos se
                // hace cumplir nosotros mismos: se guarda acá abajo en $expiresAt y lo aplica
                // el comando pagos:expirar-vencidos (corre cada minuto, ver routes/console.php).
                expiresAt: $expiresAt,
                callbackUrl: route('pagos.sip.callback'),
                singleUse: true,
            ));
        } catch (QrPaymentException $e) {
            report($e);

            return response()->json(['message' => 'No se pudo generar el QR de pago en este momento. Intenta más tarde.'], 500);
        }

        $qrImagePath = "recibos/qr/{$alias}.png";
        Storage::disk('public')->put($qrImagePath, base64_decode($result->qrImageBase64));

        $recibo = Recibo::create([
            'provider' => $provider->key(),
            'alias' => $alias,
            'nro_cliente' => $validated['nro_cliente'],
            'amount' => $monto,
            'currency' => 'BOB',
            'glosa' => $glosa,
            'status' => PaymentStatus::Pendiente,
            'expires_at' => $expiresAt,
            'qr_image_path' => $qrImagePath,
            'provider_qr_id' => $result->providerQrId,
            'provider_transaction_id' => $result->providerTransactionId,
            'destination_bank' => $result->destinationBank,
            'destination_account' => $result->destinationAccount,
            'created_by_user_id' => null,
        ]);

        return response()->json([
            'alias' => $recibo->alias,
            'qr_image_url' => Storage::disk('public')->url($qrImagePath),
            'monto' => number_format($monto, 2, '.', ''),
            'periodo' => $periodo,
            'expires_at' => $recibo->expires_at,
        ]);
    }

    /**
     * Lee el Recibo local -- no vuelve a llamar a SIP en cada sondeo, el callback
     * (SipCallbackController) ya lo mantiene al día. Nunca devuelve datos del pagador
     * (payer_name/payer_document/payer_account): este endpoint no tiene autenticación.
     */
    public function estado(string $alias): JsonResponse
    {
        $recibo = Recibo::where('alias', $alias)->first();

        if (! $recibo) {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        return response()->json([
            'status' => $recibo->status->value,
            'amount' => (string) $recibo->amount,
            'currency' => $recibo->currency,
        ]);
    }
}
