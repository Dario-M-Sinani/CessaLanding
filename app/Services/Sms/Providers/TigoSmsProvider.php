<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Exceptions\SmsException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Proveedor real de SMS vía Tigo Business (SMS API A2P, "NEW SMS A2P HTTP Interface V4.0
 * API.pdf", en la raíz del repo). Soporta dos formas de autenticarse, según lo que Tigo
 * haya entregado:
 *
 * 1. **Token directo** (`$staticToken` no vacío): Tigo entregó un bearer token ya generado
 *    (así es como se está usando hoy -- CESSA todavía no tiene client_id/client_secret para
 *    el flujo OAuth2 real). Se usa tal cual, sin pedir uno nuevo -- si vence, `send()` va a
 *    fallar con un error claro (401) y hay que pedir/pegar un token nuevo en
 *    `TIGO_SMS_TOKEN`, no hay forma de renovarlo solo sin client_id/secret.
 * 2. **OAuth2 client_credentials** (si no hay token directo pero sí `$clientId`/
 *    `$clientSecret`): token cacheado con el expires_in real que devuelve Tigo (con
 *    margen), mismo espíritu que SipQrProvider. Preparado para cuando CESSA consiga
 *    credenciales reales de este tipo.
 */
class TigoSmsProvider implements SmsProviderInterface
{
    private const TOKEN_CACHE_KEY = 'tigo_sms:auth_token';

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $sender,
        private readonly ?string $staticToken = null,
        private readonly ?string $clientId = null,
        private readonly ?string $clientSecret = null,
        private readonly int $validityPeriod = 10,
    ) {
    }

    public function key(): string
    {
        return 'tigo';
    }

    public function send(string $phone, string $message): void
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withToken($token)
            ->post("{$this->baseUrl}/v1/api/send-sms", [
                'phone' => $this->normalizePhone($phone),
                'sender' => $this->sender,
                'text' => $message,
                // El PDF de Tigo se contradice consigo mismo: las dos tablas de parámetros
                // (envío simple y masivo) describen validity_period como minutos (mín. 1,
                // máx. 4320), pero la nota al pie del ejemplo de un solo destinatario dice
                // "manejado en segundos". Se asume minutos (coincide con ambas tablas) y se
                // deja igual al TTL real del código OTP (ver ActualizarDatosController).
                'validity_period' => $this->validityPeriod,
            ]));

        if ($response->failed()) {
            throw SmsException::sendFailed($this->key(), "HTTP {$response->status()}: {$response->body()}");
        }

        $status = $response->json('message_data.0.status');

        if (in_array($status, ['REJECTD', 'EXPIRED', 'DELETED', 'UNKNOWN'], true)) {
            throw SmsException::sendFailed($this->key(), "SMS rechazado por Tigo, estado: {$status}");
        }
    }

    /**
     * Tigo espera el número con código de país sin signos (ej. "59171234567"). Los
     * formularios del sitio piden el celular boliviano local (7-8 dígitos), así que se
     * antepone el 591 si todavía no lo trae.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '591') ? $digits : "591{$digits}";
    }

    /**
     * Ejecuta $callback con un token válido. En modo OAuth2, si Tigo responde 401 (token
     * vencido/inválido) limpia el cache y reintenta una sola vez con un token nuevo. En modo
     * token directo no hay nada que renovar -- un 401 ahí sale tal cual como error (hay que
     * pedir/pegar un token nuevo a mano en TIGO_SMS_TOKEN).
     */
    private function requestWithAuth(callable $callback): Response
    {
        $token = $this->getToken();
        $response = $callback($this->http(), $token);

        if ($response->status() === 401 && ! $this->staticToken) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $token = $this->getToken();
            $response = $callback($this->http(), $token);
        }

        return $response;
    }

    private function getToken(): string
    {
        if ($this->staticToken) {
            return $this->staticToken;
        }

        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cached !== null) {
            return $cached;
        }

        $response = $this->http()
            ->asForm()
            ->post("{$this->baseUrl}/connect/token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw SmsException::sendFailed($this->key(), "Autenticación falló: HTTP {$response->status()}: {$response->body()}");
        }

        $token = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);

        if (blank($token)) {
            throw SmsException::sendFailed($this->key(), 'Autenticación falló: respuesta sin access_token');
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, max(60, $expiresIn - 60));

        return $token;
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(20);
    }
}
