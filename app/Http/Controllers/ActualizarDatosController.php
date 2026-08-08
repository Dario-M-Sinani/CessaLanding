<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionMail;
use App\Models\ClientContactUpdate;
use App\Services\CessaApiService;
use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Exceptions\SmsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Flujo público de "Actualizar Datos": el abonado se verifica con nro_cliente + N° de Cuenta
 * (mismo doble factor que Consulta de Deuda, contra SIIC) y después confirma un email y un
 * teléfono con dos códigos de un solo uso (uno por correo, otro por SMS). SIIC no tiene un
 * endpoint de escritura conocido hoy, así que el resultado se guarda en ClientContactUpdate --
 * por ahora es solo recopilación (se exporta a CSV desde /rcadmin), la migración a un sistema
 * definitivo se hace después.
 *
 * La identidad de la sesión (session()->getId()) es la que ata cada paso al siguiente: el
 * frontend nunca vuelve a mandar nro_cliente/cuenta después de verificar, así que no puede
 * saltarse el paso 1 inventando datos de una cuenta ajena.
 */
class ActualizarDatosController extends Controller
{
    private const SESSION_KEY = 'actualizar_datos.cuenta';

    private const CUENTA_VERIFICADA_TTL_MINUTES = 15;

    private const OTP_TTL_MINUTES = 10;

    private const OTP_MAX_INTENTOS = 5;

    public function __construct(
        private readonly CessaApiService $apiService,
        private readonly SmsProviderInterface $smsProvider,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('ActualizarDatos');
    }

    public function verificarCuenta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nro_cliente' => ['required', 'digits_between:1,10'],
            'zona' => ['required', 'digits_between:1,3'],
            'manzano' => ['required', 'digits_between:1,4'],
            'correlativo' => ['required', 'digits_between:1,6'],
        ]);

        try {
            $data = $this->apiService->consultaDeuda([
                'nro_cliente' => $validated['nro_cliente'],
                'ver_deuda' => 'no',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con el sistema comercial SIIC. Intente más tarde.',
            ], 503);
        }

        if (isset($data['error']) || empty($data['nro_cliente'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún abonado con ese número.',
            ], 422);
        }

        // Mismo criterio que ConsultaDeudaController: SIIC devuelve zona/manzano/correlativo
        // sin ceros a la izquierda, se compara como número. Mensaje genérico si no coincide.
        if (
            (int) ($data['zona'] ?? -1) !== (int) $validated['zona']
            || (int) ($data['manzano'] ?? -1) !== (int) $validated['manzano']
            || (int) ($data['correlativo'] ?? -1) !== (int) $validated['correlativo']
        ) {
            Log::warning('actualizar_datos.cuenta_no_coincide', [
                'ip' => $request->ip(),
                'nro_cliente' => $validated['nro_cliente'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Los datos ingresados no coinciden con ningún abonado registrado.',
            ], 422);
        }

        $cuenta = sprintf(
            '%02d-%03d-%05d',
            (int) $validated['zona'],
            (int) $validated['manzano'],
            (int) $validated['correlativo'],
        );

        $request->session()->put(self::SESSION_KEY, [
            'nro_cliente' => $data['nro_cliente'],
            'cuenta' => $cuenta,
            'nombre' => $data['nombre'] ?? null,
            'verified_at' => now()->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'nombre' => $data['nombre'] ?? null,
        ]);
    }

    public function enviarCodigos(Request $request): JsonResponse
    {
        $cuenta = $this->cuentaVerificada($request);

        if (! $cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Tu verificación expiró. Vuelve a ingresar tu N° de Cliente y N° de Cuenta.',
            ], 419);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'digits_between:7,15'],
        ]);

        $throttleKey = 'actualizar-datos-otp:'.$request->session()->getId();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos de envío. Espera unos minutos y vuelve a intentar.',
            ], 429);
        }

        RateLimiter::hit($throttleKey, 600);

        $codigoEmail = (string) random_int(100000, 999999);
        $codigoSms = (string) random_int(100000, 999999);

        Cache::put('actualizar_datos:otp:'.$request->session()->getId(), [
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'email_code_hash' => Hash::make($codigoEmail),
            'phone_code_hash' => Hash::make($codigoSms),
            'intentos' => 0,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($validated['email'])->send(new CodigoVerificacionMail($codigoEmail, $cuenta['nombre']));
        } catch (\Exception $e) {
            Log::error('actualizar_datos.envio_email_fallo', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el código al correo indicado. Verifica la dirección e intenta de nuevo.',
            ], 500);
        }

        try {
            $this->smsProvider->send(
                $validated['phone'],
                "CESSA: tu codigo de verificacion es {$codigoSms}. Vence en 10 minutos.",
            );
        } catch (SmsException $e) {
            Log::error('actualizar_datos.envio_sms_fallo', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el código al celular indicado. Verifica el número e intenta de nuevo.',
            ], 500);
        }

        return response()->json(['success' => true]);
    }

    public function confirmarCodigos(Request $request): JsonResponse
    {
        $cuenta = $this->cuentaVerificada($request);

        if (! $cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Tu verificación expiró. Vuelve a ingresar tu N° de Cliente y N° de Cuenta.',
            ], 419);
        }

        $validated = $request->validate([
            'codigo_email' => ['required', 'digits:6'],
            'codigo_sms' => ['required', 'digits:6'],
        ]);

        $otpCacheKey = 'actualizar_datos:otp:'.$request->session()->getId();
        $otp = Cache::get($otpCacheKey);

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Los códigos vencieron. Solicita un nuevo envío.',
            ], 419);
        }

        if ($otp['intentos'] >= self::OTP_MAX_INTENTOS) {
            Cache::forget($otpCacheKey);

            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Solicita un nuevo envío de códigos.',
            ], 429);
        }

        $emailOk = Hash::check($validated['codigo_email'], $otp['email_code_hash']);
        $smsOk = Hash::check($validated['codigo_sms'], $otp['phone_code_hash']);

        if (! $emailOk || ! $smsOk) {
            $otp['intentos']++;
            Cache::put($otpCacheKey, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));

            return response()->json([
                'success' => false,
                'message' => 'Uno o ambos códigos son incorrectos.',
            ], 422);
        }

        ClientContactUpdate::updateOrCreate(
            ['nro_cliente' => $cuenta['nro_cliente'], 'cuenta' => $cuenta['cuenta']],
            [
                'client_name' => $cuenta['nombre'],
                'email' => $otp['email'],
                'phone' => $otp['phone'],
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ],
        );

        Cache::forget($otpCacheKey);
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['success' => true]);
    }

    /**
     * @return array{nro_cliente: string, cuenta: string, nombre: ?string}|null
     */
    private function cuentaVerificada(Request $request): ?array
    {
        $cuenta = $request->session()->get(self::SESSION_KEY);

        if (! $cuenta) {
            return null;
        }

        $vencio = now()->timestamp - $cuenta['verified_at'] > self::CUENTA_VERIFICADA_TTL_MINUTES * 60;

        if ($vencio) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $cuenta;
    }
}
