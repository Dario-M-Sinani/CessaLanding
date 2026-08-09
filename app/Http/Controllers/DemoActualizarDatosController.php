<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionMail;
use App\Services\CessaApiService;
use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Exceptions\SmsException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Versión de prueba/demo del flujo "Actualizar Datos" (App\Http\Controllers\ActualizarDatosController),
 * pensada para ser llamada desde un sitio estático completamente aparte (otro origen/hosting),
 * no desde el sitio institucional. La diferencia con la versión real es que acá no hay sesión
 * ni cookies compartidas entre ese sitio y este backend -- el estado entre pasos viaja en un
 * token cifrado (Crypt::encryptString) que el propio cliente reenvía en cada llamada. El token
 * nunca puede ser fabricado ni leído por el cliente (usa APP_KEY), solo lo emite y lo entiende
 * este backend, así que sigue sin poder saltarse la verificación real contra SIIC.
 *
 * La verificación (SIIC) y el doble código (correo + SMS) son EXACTAMENTE los mismos que en el
 * flujo de producción, reutilizando los mismos servicios (CessaApiService, SmsProviderInterface,
 * CodigoVerificacionMail) -- es una interfaz distinta sobre la misma lógica real, no una
 * simulación. A propósito NO escribe en ClientContactUpdate (esa tabla es la recopilación real
 * del sitio institucional): acá el resultado verificado se lo queda el propio sitio estático,
 * que arma su CSV en el navegador.
 */
class DemoActualizarDatosController extends Controller
{
    private const CUENTA_TTL_MINUTES = 15;

    private const OTP_TTL_MINUTES = 10;

    private const OTP_MAX_INTENTOS = 5;

    public function __construct(
        private readonly CessaApiService $apiService,
        private readonly SmsProviderInterface $smsProvider,
    ) {
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

        if (
            (int) ($data['zona'] ?? -1) !== (int) $validated['zona']
            || (int) ($data['manzano'] ?? -1) !== (int) $validated['manzano']
            || (int) ($data['correlativo'] ?? -1) !== (int) $validated['correlativo']
        ) {
            Log::warning('demo_actualizar_datos.cuenta_no_coincide', [
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

        $token = $this->encode([
            'paso' => 'verificado',
            'nro_cliente' => $data['nro_cliente'],
            'cuenta' => $cuenta,
            'nombre' => $data['nombre'] ?? null,
            'exp' => now()->addMinutes(self::CUENTA_TTL_MINUTES)->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'nombre' => $data['nombre'] ?? null,
            'token' => $token,
        ]);
    }

    public function enviarCodigos(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'digits_between:7,15'],
        ]);

        $cuenta = $this->decode($validated['token'], 'verificado');

        if (! $cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Tu verificación expiró. Vuelve a ingresar tu N° de Cliente y N° de Cuenta.',
            ], 419);
        }

        $throttleKey = 'demo-actualizar-datos-otp:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos de envío. Espera unos minutos y vuelve a intentar.',
            ], 429);
        }

        RateLimiter::hit($throttleKey, 600);

        $codigoEmail = (string) random_int(100000, 999999);
        $codigoSms = (string) random_int(100000, 999999);

        $token = $this->encode([
            'paso' => 'codigos_enviados',
            'nro_cliente' => $cuenta['nro_cliente'],
            'cuenta' => $cuenta['cuenta'],
            'nombre' => $cuenta['nombre'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'email_code_hash' => Hash::make($codigoEmail),
            'phone_code_hash' => Hash::make($codigoSms),
            'intentos' => 0,
            'exp' => now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp,
        ]);

        try {
            Mail::to($validated['email'])->send(new CodigoVerificacionMail($codigoEmail, $cuenta['nombre']));
        } catch (\Exception $e) {
            Log::error('demo_actualizar_datos.envio_email_fallo', ['error' => $e->getMessage()]);

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
            Log::error('demo_actualizar_datos.envio_sms_fallo', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el código al celular indicado. Verifica el número e intenta de nuevo.',
            ], 500);
        }

        return response()->json(['success' => true, 'token' => $token]);
    }

    public function confirmarCodigos(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'codigo_email' => ['required', 'digits:6'],
            'codigo_sms' => ['required', 'digits:6'],
        ]);

        $otp = $this->decode($validated['token'], 'codigos_enviados');

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Los códigos vencieron. Solicita un nuevo envío.',
            ], 419);
        }

        if ($otp['intentos'] >= self::OTP_MAX_INTENTOS) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Solicita un nuevo envío de códigos.',
            ], 429);
        }

        $emailOk = Hash::check($validated['codigo_email'], $otp['email_code_hash']);
        $smsOk = Hash::check($validated['codigo_sms'], $otp['phone_code_hash']);

        if (! $emailOk || ! $smsOk) {
            $otp['intentos']++;

            return response()->json([
                'success' => false,
                'message' => 'Uno o ambos códigos son incorrectos.',
                'token' => $this->encode($otp),
            ], 422);
        }

        // A propósito NO se guarda en ClientContactUpdate (esa tabla es la recopilación real
        // del sitio institucional) -- esta es la versión demo, solo verifica de verdad; el
        // resultado se lo queda el propio sitio estático para armar su CSV en el navegador.
        return response()->json([
            'success' => true,
            'registro' => [
                'nro_cliente' => $otp['nro_cliente'],
                'cuenta' => $otp['cuenta'],
                'nombre' => $otp['nombre'],
                'email' => $otp['email'],
                'phone' => $otp['phone'],
            ],
        ]);
    }

    private function encode(array $payload): string
    {
        // 'exp' ya viene armado por el caller salvo cuando reencodeamos el mismo payload de
        // vuelta (intento fallido) -- ahí conserva el 'exp' original, no se renueva el vencimiento.
        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(string $token, string $pasoEsperado): ?array
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (DecryptException) {
            return null;
        }

        if (! is_array($payload) || ($payload['paso'] ?? null) !== $pasoEsperado) {
            return null;
        }

        if (($payload['exp'] ?? 0) < now()->timestamp) {
            return null;
        }

        return $payload;
    }
}
