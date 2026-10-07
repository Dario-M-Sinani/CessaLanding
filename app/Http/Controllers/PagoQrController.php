<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\User;
use App\Services\CessaApiService;
use App\Services\Cobranzas\FacturacionRecibo;
use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentProviderRegistry;
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

    protected PaymentProviderRegistry $providers;

    public function __construct(CessaApiService $apiService, PaymentProviderRegistry $providers)
    {
        $this->apiService = $apiService;
        $this->providers = $providers;
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
            // Banco con el que se genera el QR (lo elige el cliente en el modal). Si no se
            // manda, se usa el de siempre (SIP/BISA) para no romper clientes viejos.
            'banco' => ['nullable', 'string', 'in:'.implode(',', $this->providers->selectableKeys())],
            // Solo pruebas: botón "Simular pago" de Consulta de Deuda (ver simulacionPermitida()).
            'simular' => ['nullable', 'boolean'],
        ]);

        $simular = (bool) ($validated['simular'] ?? false);

        if ($simular && ! self::simulacionPermitida()) {
            abort(404);
        }

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

        // Un cliente no puede tener dos QR vivos a la vez. Si ya tiene uno Pendiente y todavía
        // vigente (no vencido) por EXACTAMENTE lo mismo (mismos comprobantes, mismo monto), se
        // DEVUELVE ese mismo QR en vez de generar otro -- así, si cerró el modal por equivocación,
        // al reabrir ve el mismo código y no se acumulan QR duplicados en el banco. Si eligió otra
        // cantidad de meses (otro monto), el anterior NO sirve: sigue de largo y el bloque de abajo
        // lo inhabilita en el banco antes de generar el nuevo. Se compara `expires_at` por fecha
        // porque pagos:expirar-vencidos puede no haber corrido. Va después de verificar la cuenta
        // contra SIIC, para no exponer el QR de un cliente a quien solo conoce su número sin el
        // N° de cuenta.
        $qrActivo = Recibo::where('nro_cliente', $validated['nro_cliente'])
            ->where('status', PaymentStatus::Pendiente)
            ->where('expires_at', '>', now())
            ->whereNotNull('qr_image_path')
            ->latest('expires_at')
            ->first();

        if ($qrActivo && ! $simular && $this->mismaSeleccion($qrActivo, $aPagar->all(), $monto)) {
            Log::info('pago_qr.reusa_vigente', ['alias' => $qrActivo->alias]);

            return response()->json($this->reciboPayload($qrActivo));
        }

        $MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $primero = $aPagar->first();
        $ultimo = $aPagar->last();

        // Legible, para mostrarle al cliente en el modal de pago (sin límite de caracteres).
        $periodo = $primero === $ultimo
            ? "{$MESES[(int) $primero['mes']]}/{$primero['anio']}"
            : "{$MESES[(int) $primero['mes']]}-{$MESES[(int) $ultimo['mes']]}/{$ultimo['anio']}";

        // Glosa que viaja al QR/banco: SIP la corta a 30 caracteres. Ahora que existe la
        // descripción de constancia interna (abajo, sin límite) con el detalle completo de
        // fechas, la glosa se simplifica a un formato genérico y fijo -- "CESSA {cantidad de
        // comprobantes} comp {N° cliente} B{monto con 2 decimales}" -- sin fechas, así nunca
        // se acerca al límite salvo en el caso extremo de un cliente con muchos dígitos y
        // muchos comprobantes, donde se saca el prefijo "CESSA " (única parte prescindible,
        // el resto -- cantidad, cliente, monto -- nunca se debe truncar). Probado con Tinker
        // el peor caso real posible (cliente de 10 dígitos, 24 comprobantes, Bs. 50.000): entra
        // justo sin el prefijo (28 caracteres).
        $montoTexto = number_format($monto, 2, '.', '');
        $glosaConPrefijo = "CESSA {$cantidadMeses} comp {$validated['nro_cliente']} B{$montoTexto}";
        $glosaSinPrefijo = "{$cantidadMeses} comp {$validated['nro_cliente']} B{$montoTexto}";
        $glosa = mb_strlen($glosaConPrefijo) <= 30 ? $glosaConPrefijo : $glosaSinPrefijo;

        // Descripción de constancia interna: mismos datos que antes armaba la glosa (y más),
        // sin el límite de 30 caracteres de SIP -- todo va completo y sin abreviar: "bolivianos"
        // en vez de "Bs", "comprobante(s)" además de "mes(es)" (cada aviso pendiente pagado es
        // un comprobante), fecha exacta de cada periodo con año completo (no solo el rango
        // "Enero-Marzo/2026"), y "Cliente" sin abreviar.
        $mesesTexto = $cantidadMeses === 1 ? '1 mes' : "{$cantidadMeses} meses";
        $comprobantesTexto = $cantidadMeses === 1 ? '1 comprobante' : "{$cantidadMeses} comprobantes";
        $periodosExactos = $aPagar->map(fn (array $item) => "{$MESES[(int) $item['mes']]} {$item['anio']}")->all();
        $fechasExactas = count($periodosExactos) > 1
            ? implode(', ', array_slice($periodosExactos, 0, -1)).' y '.end($periodosExactos)
            : $periodosExactos[0];
        // Monto "legible" sin ceros decimales de más (1489, no 1489.00) -- distinto del
        // $montoTexto de la glosa, que sí necesita los 2 decimales fijos siempre.
        $montoLegible = floor($monto) == $monto
            ? number_format($monto, 0, '.', '')
            : number_format($monto, 2, '.', '');
        $descripcionPago = "Pago de {$mesesTexto} ({$comprobantesTexto}) por {$montoLegible} bolivianos"
            ." — Cliente {$validated['nro_cliente']} — Períodos: {$fechasExactas}";

        // El usuario genera un solo QR sin elegir banco: el sistema alterna entre BISA y BNB en
        // cada generación (ver PaymentProviderRegistry::rotateNext) para repartir las generaciones
        // entre los dos bancos, según el banco del último Recibo creado. Si algún día se quisiera
        // dejar elegir el banco, `banco` en el request tiene prioridad.
        $bancoKey = $validated['banco'] ?? $this->providers->rotateNext(Recibo::latest('id')->value('provider'));

        $provider = $this->providers->get($bancoKey);
        $alias = 'CESSA-WEB-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        $expiresAt = now()->addMinutes(5);

        // Pago simulado (solo pruebas): no se toca ningún banco ni los QR pendientes del cliente.
        // Se crea el Recibo ya Pagado con la deuda real de arriba, como si hubiera llegado el
        // callback; de ahí sigue el circuito real (el cron factura, el modal muestra el resultado).
        if ($simular) {
            $email = auth()->user()->email;
            $recibo = Recibo::create([
                'provider' => $provider->key(),
                'alias' => 'CESSA-SIM-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'nro_cliente' => $validated['nro_cliente'],
                'amount' => $monto,
                'currency' => 'BOB',
                'glosa' => $glosa,
                'descripcion_pago' => $descripcionPago,
                'debt_items' => $aPagar->values()->all(),
                'status' => PaymentStatus::Pagado,
                'expires_at' => $expiresAt,
                'paid_at' => now(),
                'payer_name' => 'PAGO SIMULADO (prueba)',
                'callback_payload' => ['simulado' => true, 'por' => $email, 'at' => now()->toIso8601String()],
                'created_by_user_id' => null,
            ]);

            Log::warning('pago_qr.pago_simulado', ['alias' => $recibo->alias, 'user' => $email]);
            FacturacionRecibo::facturarTrasRespuesta($recibo);

            return response()->json($this->reciboPayload($recibo));
        }

        // Evita que un mismo cliente termine con más de un QR válido a la vez (p.ej. si
        // vuelve a tocar "Pagar con QR" con uno anterior todavía sin pagar): se inhabilita
        // en SIP y localmente cualquier QR pendiente previo de este nro_cliente antes de
        // generar el nuevo. `lockForUpdate` evita que dos requests casi simultáneas del
        // mismo cliente dejen dos recibos "pendiente" en pie a la vez.
        DB::transaction(function () use ($validated) {
            $anteriores = Recibo::where('nro_cliente', $validated['nro_cliente'])
                ->where('status', PaymentStatus::Pendiente)
                ->lockForUpdate()
                ->get();

            foreach ($anteriores as $anterior) {
                // Si esto pasa con un QR todavía vigente, el reuso de arriba falló: queda
                // registrado para poder diagnosticarlo (ver §-1duoquinquagies del doc de continuidad).
                Log::info('pago_qr.inhabilita_anterior', [
                    'alias' => $anterior->alias,
                    'expires_at' => (string) $anterior->expires_at,
                    'now' => (string) now(),
                    'vigente' => $anterior->expires_at > now(),
                ]);

                try {
                    // Un QR pendiente anterior pudo haberse generado con otro banco: se da de
                    // baja con el proveedor con el que se creó (Recibo::provider), no con el
                    // que se eligió ahora.
                    $this->providers->forRecibo($anterior)->disable($anterior->alias);
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
            'descripcion_pago' => $descripcionPago,
            // Snapshot exacto de lo que se está pagando -- hace falta tal cual para registrar
            // la factura real contra api-cobranzas-bancos una vez que el pago se confirma (ver
            // FacturacionRecibo). $aPagar ya viene con `importe_firmado` calculado arriba, que
            // no es un campo real de SIIC -- se guarda igual por si sirve de referencia, pero
            // no reemplaza a `importe` (el campo real que probablemente espere "pagar").
            'debt_items' => $aPagar->values()->all(),
            'status' => PaymentStatus::Pendiente,
            'expires_at' => $expiresAt,
            'qr_image_path' => $qrImagePath,
            'provider_qr_id' => $result->providerQrId,
            'provider_transaction_id' => $result->providerTransactionId,
            'destination_bank' => $result->destinationBank,
            'destination_account' => $result->destinationAccount,
            'created_by_user_id' => null,
        ]);

        return response()->json($this->reciboPayload($recibo));
    }

    /**
     * Datos públicos de un Recibo para el modal de pago (sin PII del pagador): alias, imagen del
     * QR, monto, periodo legible y vencimiento. Se usa tanto al crear un QR nuevo como al devolver
     * uno que sigue vigente.
     *
     * @return array<string, mixed>
     */
    private function reciboPayload(Recibo $recibo): array
    {
        return [
            'alias' => $recibo->alias,
            'status' => $recibo->status->value,
            'qr_image_url' => $recibo->qr_image_path ? Storage::disk('public')->url($recibo->qr_image_path) : null,
            'monto' => number_format((float) $recibo->amount, 2, '.', ''),
            'periodo' => $this->periodoDeItems($recibo->debt_items ?? []),
            'expires_at' => $recibo->expires_at,
        ];
    }

    /**
     * ¿El QR vigente cobra exactamente lo mismo que se pide ahora? Mismo monto y mismos
     * comprobantes (año, mes y N° de comprobante, en el mismo orden).
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function mismaSeleccion(Recibo $recibo, array $items, float $monto): bool
    {
        $claves = fn (array $lista) => array_map(
            fn (array $i) => ((int) ($i['anio'] ?? 0)).'-'.((int) ($i['mes'] ?? 0)).'-'.trim((string) ($i['nro_comprobante'] ?? '')),
            array_values($lista),
        );

        return round((float) $recibo->amount, 2) === round($monto, 2)
            && $claves($recibo->debt_items ?? []) === $claves($items);
    }

    /**
     * Periodo legible ("Enero/2026" o "Enero-Marzo/2026") a partir del snapshot de deuda del
     * Recibo (debt_items, ya ordenado del más antiguo al más reciente).
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function periodoDeItems(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $primero = $items[array_key_first($items)];
        $ultimo = $items[array_key_last($items)];

        return $primero === $ultimo
            ? "{$MESES[(int) $primero['mes']]}/{$primero['anio']}"
            : "{$MESES[(int) $primero['mes']]}-{$MESES[(int) $ultimo['mes']]}/{$ultimo['anio']}";
    }

    /**
     * Solo para pruebas: si la simulación está habilitada (PAGOS_SIMULACION_HABILITADA) y quien
     * mira la página está logueado en el panel con rol SYSTEM, el modal del QR muestra el botón
     * "Simular pago".
     */
    public static function simulacionPermitida(): bool
    {
        $user = auth()->user();

        return (bool) config('services.pagos.simulacion_habilitada')
            && $user instanceof User
            && $user->hasRole(User::ROLE_SYSTEM);
    }

    /**
     * Marca Pagado un QR Pendiente sin que pase plata, como si hubiera llegado el callback del
     * banco: de ahí en adelante sigue el circuito real (pagos:registrar-facturacion factura,
     * el modal muestra "¡Pago recibido!" y el comprobante). Antes se inhabilita el QR en el banco,
     * así nadie puede pagarlo de verdad después: ese pago real se ignoraría por idempotencia y
     * la plata quedaría sin registrar. Fuera de la simulación responde 404, como si no existiera.
     */
    public function simular(string $alias): JsonResponse
    {
        if (! self::simulacionPermitida()) {
            abort(404);
        }

        $recibo = Recibo::where('alias', $alias)->first();

        if (! $recibo) {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        if ($recibo->status !== PaymentStatus::Pendiente) {
            return response()->json(['message' => "El QR no está pendiente (estado: {$recibo->status->value})."], 422);
        }

        try {
            $this->providers->forRecibo($recibo)->disable($recibo->alias);
        } catch (QrPaymentException $e) {
            report($e);

            return response()->json(['message' => 'No se pudo inhabilitar el QR en el banco; no se simula el pago para que no quede pagable de verdad.'], 502);
        }

        $email = auth()->user()->email;

        $recibo->update([
            'status' => PaymentStatus::Pagado,
            'paid_at' => now(),
            'payer_name' => 'PAGO SIMULADO (prueba)',
            'callback_payload' => [
                'simulado' => true,
                'por' => $email,
                'at' => now()->toIso8601String(),
            ],
        ]);

        Log::warning('pago_qr.pago_simulado', ['alias' => $recibo->alias, 'user' => $email]);
        FacturacionRecibo::facturarTrasRespuesta($recibo);

        return response()->json(['status' => $recibo->status->value]);
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
            'comprobante_url' => $recibo->comprobante_path
                ? Storage::disk('public')->url($recibo->comprobante_path)
                : null,
        ]);
    }
}
