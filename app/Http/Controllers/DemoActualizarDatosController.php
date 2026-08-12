<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionMail;
use App\Models\DemoActualizarDatosRegistro;
use App\Models\DemoConfiguracion;
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
        // del sitio institucional) -- esta es la versión demo, solo verifica de verdad. El
        // resultado principal se lo queda el propio sitio estático (localStorage) para armar
        // su CSV, pero además se guarda acá un respaldo aislado (SQLite aparte, ver
        // DemoActualizarDatosRegistro) para que ese localStorage no sea la única copia.
        $capturadoEn = now();

        DemoActualizarDatosRegistro::create([
            'nro_cliente' => $otp['nro_cliente'],
            'cuenta' => $otp['cuenta'],
            'nombre' => $otp['nombre'],
            'email' => $otp['email'],
            'phone' => $otp['phone'],
            'capturado_en' => $capturadoEn,
        ]);

        return response()->json([
            'success' => true,
            'registro' => [
                'nro_cliente' => $otp['nro_cliente'],
                'cuenta' => $otp['cuenta'],
                'nombre' => $otp['nombre'],
                'email' => $otp['email'],
                'phone' => $otp['phone'],
                'capturado_en' => $capturadoEn->toIso8601String(),
            ],
        ]);
    }

    /**
     * Login de la página oculta de administración (Admin.vue): valida usuario/contraseña
     * (DEMO_ADMIN_USERNAME/PASSWORD en .env, mismo criterio simple que
     * VerifySipCallbackAuth) y devuelve el token compartido que ya protegía `registros()`/
     * `actualizarTokenSms()` -- así nadie tiene que copiar/pegar ese token a mano, y no
     * queda expuesto en pantalla como texto plano en el frontend.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'demo-admin-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json(['success' => false, 'message' => 'Demasiados intentos. Espera unos minutos.'], 429);
        }

        $usernameOk = config('services.demo_actualizar_datos.admin_username');
        $passwordOk = config('services.demo_actualizar_datos.admin_password');

        $valido = filled($usernameOk) && filled($passwordOk)
            && hash_equals($usernameOk, $validated['username'])
            && hash_equals($passwordOk, $validated['password']);

        if (! $valido) {
            RateLimiter::hit($throttleKey, 300);
            Log::warning('demo_actualizar_datos.login_fallido', ['ip' => $request->ip()]);

            return response()->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos.'], 401);
        }

        RateLimiter::clear($throttleKey);

        return response()->json(['success' => true, 'token' => config('services.demo_actualizar_datos.admin_token')]);
    }

    /**
     * Listado del respaldo en SQLite, para la página oculta de administración de la
     * herramienta aparte (no la principal, que solo muestra un contador). Protegido por un
     * token compartido simple (no es autenticación real, es la misma filosofía de "ruta
     * oculta" del frontend, pero al menos exige conocer un secreto, no solo la URL) -- ver
     * DEMO_ACTUALIZAR_DATOS_ADMIN_TOKEN en .env.
     */
    public function registros(Request $request): JsonResponse
    {
        if (! $this->tokenAdminValido($request)) {
            return response()->json(['success' => false, 'message' => 'Token inválido.'], 403);
        }

        $registros = DemoActualizarDatosRegistro::orderByDesc('capturado_en')->get([
            'nro_cliente', 'cuenta', 'nombre', 'email', 'phone', 'capturado_en',
        ]);

        return response()->json(['success' => true, 'registros' => $registros]);
    }

    /**
     * Actualiza el token de Tigo SMS en caliente (ver DemoConfiguracion + SmsServiceProvider)
     * desde la página oculta de administración -- sin esto, cada vez que vence (24h en el
     * primero que se probó) había que editar TIGO_SMS_TOKEN en .env a mano y reiniciar el
     * servidor. Mismo token compartido de admin que `registros()`.
     */
    public function actualizarTokenSms(Request $request): JsonResponse
    {
        if (! $this->tokenAdminValido($request)) {
            return response()->json(['success' => false, 'message' => 'Token inválido.'], 403);
        }

        $validated = $request->validate([
            'sms_token' => ['required', 'string', 'min:10'],
        ]);

        DemoConfiguracion::guardar('tigo_sms_token', trim($validated['sms_token']));

        return response()->json(['success' => true]);
    }

    private function tokenAdminValido(Request $request): bool
    {
        $tokenEsperado = config('services.demo_actualizar_datos.admin_token');

        return filled($tokenEsperado) && hash_equals($tokenEsperado, (string) $request->input('token'));
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
