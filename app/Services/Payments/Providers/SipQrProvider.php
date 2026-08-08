<?php

namespace App\Services\Payments\Providers;

use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\DataTransferObjects\QrPaymentResult;
use App\Services\Payments\DataTransferObjects\QrPaymentStatusResult;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * Pasarela SIP (de MC4, https://mc4.com.bo) -- la cuenta destino configurada hoy es de
 * Banco BISA. Implementa el flujo descrito en "Especificación de Servicios SIP - Empresa":
 * token de 1h (se cachea y se reutiliza), generar/inhabilitar/consultar QR con apikeyServicio.
 *
 * Nunca se desactiva la verificación TLS acá (ver ESTADO_SEGURIDAD_MIGRACION.md, hallazgo #6
 * de la auditoría -- ese mismo error ya se cometió una vez con la integración a SIIC).
 */
class SipQrProvider implements QrPaymentProviderInterface
{
    private const TOKEN_CACHE_KEY = 'sip:auth_token';

    private const TOKEN_TTL_SECONDS = 55 * 60; // el token real dura 1h; se cachea un poco menos por margen.

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apikey,
        private readonly string $username,
        private readonly string $password,
        private readonly string $apikeyServicio,
    ) {
    }

    public function key(): string
    {
        return 'sip_bisa';
    }

    public function generate(QrPaymentRequest $request): QrPaymentResult
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withHeaders(['apikeyServicio' => $this->apikeyServicio, 'Authorization' => "Bearer {$token}"])
            ->post("{$this->baseUrl}/api/v1/generaQr", [
                'alias' => $request->alias,
                'callback' => $request->callbackUrl,
                // SIP limita la glosa a 30 caracteres (ver historial de versiones del doc).
                'detalleGlosa' => mb_substr($request->description, 0, 30),
                'monto' => round($request->amount, 2),
                'moneda' => $request->currency,
                'fechaVencimiento' => $request->expiresAt->format('d/m/Y'),
                'tipoSolicitud' => 'API',
                'unicoUso' => $request->singleUse ? 'true' : 'false',
            ]));

        $body = $this->decode($response, 'generar QR', successCode: '0000');
        $objeto = $body['objeto'];

        return new QrPaymentResult(
            qrImageBase64: $objeto['imagenQr'],
            providerQrId: (string) $objeto['idQr'],
            providerTransactionId: (string) $objeto['idTransaccion'],
            expiresAt: Carbon::createFromFormat('d/m/Y', $objeto['fechaVencimiento'])->endOfDay(),
            destinationBank: $objeto['bancoDestino'],
            destinationAccount: $objeto['cuentaDestino'],
        );
    }

    public function disable(string $alias): void
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withHeaders(['apikeyServicio' => $this->apikeyServicio, 'Authorization' => "Bearer {$token}"])
            ->post("{$this->baseUrl}/api/v1/inhabilitarPago", [
                'alias' => $alias,
            ]));

        $this->decode($response, 'inhabilitar QR', successCode: '0000');
    }

    public function status(string $alias): QrPaymentStatusResult
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withHeaders(['apikeyServicio' => $this->apikeyServicio, 'Authorization' => "Bearer {$token}"])
            ->post("{$this->baseUrl}/api/v1/estadoTransaccion", [
                'alias' => $alias,
            ]));

        $body = $this->decode($response, 'consultar estado', successCode: '0000');
        $objeto = $body['objeto'];

        return new QrPaymentStatusResult(
            alias: $objeto['alias'],
            status: $this->mapStatus($objeto['estadoActual']),
            processedAt: filled($objeto['fechaProcesamiento'] ?? null)
                ? Carbon::parse($objeto['fechaProcesamiento'])
                : null,
            providerOrderNumber: $objeto['numeroOrdenOriginante'] ?? null,
            amount: isset($objeto['monto']) ? (float) $objeto['monto'] : null,
            providerQrId: $objeto['idQr'] ?? null,
            currency: $objeto['moneda'] ?? null,
            payerAccount: $objeto['cuentaCliente'] ?? null,
            payerName: $objeto['nombreCliente'] ?? null,
            payerDocument: $objeto['documentoCliente'] ?? null,
        );
    }

    /**
     * SIP usa nombres de estado en español/mayúsculas propios; se traducen acá al enum
     * compartido para que el resto de la app no dependa del vocabulario de este proveedor.
     */
    private function mapStatus(string $estado): PaymentStatus
    {
        return match (mb_strtoupper($estado)) {
            'PAGADO' => PaymentStatus::Pagado,
            'INHABILITADO' => PaymentStatus::Inhabilitado,
            'EXPIRADO' => PaymentStatus::Expirado,
            'ERROR' => PaymentStatus::Error,
            default => PaymentStatus::Pendiente,
        };
    }

    /**
     * Ejecuta $callback con un token válido; si SIP responde 401 (token vencido/inválido),
     * limpia el cache y reintenta una sola vez con un token nuevo.
     */
    private function requestWithAuth(callable $callback): Response
    {
        $token = $this->getToken();
        $response = $callback($this->http(), $token);

        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $token = $this->getToken(forceRefresh: true);
            $response = $callback($this->http(), $token);
        }

        return $response;
    }

    private function getToken(bool $forceRefresh = false): string
    {
        if ($forceRefresh) {
            Cache::forget(self::TOKEN_CACHE_KEY);
        }

        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_TTL_SECONDS, function () {
            $response = $this->http()
                ->withHeaders(['apikey' => $this->apikey])
                ->post("{$this->baseUrl}/autenticacion/v1/generarToken", [
                    'username' => $this->username,
                    'password' => $this->password,
                ]);

            if ($response->failed()) {
                throw QrPaymentException::authenticationFailed($this->key(), "HTTP {$response->status()}: {$response->body()}");
            }

            $body = $response->json();

            if (($body['codigo'] ?? null) !== 'OK') {
                throw QrPaymentException::authenticationFailed($this->key(), $body['mensaje'] ?? 'respuesta sin código OK');
            }

            return $body['objeto']['token'];
        });
    }

    private function http(): PendingRequest
    {
        return Http::asJson()->acceptJson()->timeout(20);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response, string $operation, string $successCode): array
    {
        if ($response->failed() && $response->status() !== 400) {
            throw QrPaymentException::requestFailed($this->key(), $operation, "HTTP {$response->status()}: {$response->body()}");
        }

        $body = $response->json();

        if (! is_array($body) || ($body['codigo'] ?? null) !== $successCode) {
            $mensaje = is_array($body) ? ($body['mensaje'] ?? 'respuesta inesperada') : $response->body();

            throw QrPaymentException::requestFailed($this->key(), $operation, $mensaje);
        }

        return $body;
    }
}
