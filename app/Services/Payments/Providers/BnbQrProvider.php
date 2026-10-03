<?php

namespace App\Services\Payments\Providers;

use App\Models\Recibo;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\DataTransferObjects\QrPaymentResult;
use App\Services\Payments\DataTransferObjects\QrPaymentStatusResult;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Pasarela QR Simple del Banco Nacional de Bolivia (Api Market) -- segundo banco elegible junto
 * a SIP/BISA. Implementa el flujo del PDF "Open Banking Medio de Pago QR - Notificación V2":
 * token (accountId/authorizationId) que se cachea, generar/consultar/cancelar QR, y la
 * notificación de pago la recibe BnbCallbackController (formato propio del BNB, ver esa clase).
 *
 * Diferencias con SIP que obligan a puentes acá:
 *  - BNB NO maneja `alias`: identifica cada QR por un `id` numérico propio. Para reconciliar el
 *    aviso de pago con nuestro Recibo se manda el alias en `additionalData` al generar (BNB lo
 *    devuelve en la notificación) y además se guarda su `id` en Recibo::provider_qr_id. Por eso
 *    disable()/status(), que reciben solo el alias, traducen alias -> provider_qr_id leyendo el
 *    Recibo (SIP no lo necesita porque él sí conoce el alias).
 *  - BNB NO recibe una URL de callback por QR: la de notificación se configura una sola vez en
 *    el portal del BNB. El $request->callbackUrl del contrato se ignora acá a propósito.
 *  - La respuesta de generación no trae banco/cuenta destino ni idTransaccion: se completan con
 *    valores propios (nombre del banco fijo, la cuenta = destinationAccountId configurado).
 *
 * Nunca se desactiva la verificación TLS (mismo criterio que SipQrProvider).
 */
class BnbQrProvider implements QrPaymentProviderInterface
{
    private const TOKEN_CACHE_KEY = 'bnb:auth_token';

    // El token del BNB no documenta duración; se cachea corto por margen y se refresca ante 401.
    private const TOKEN_TTL_SECONDS = 50 * 60;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $accountId,
        private readonly string $authorizationId,
        private readonly string $currency,
        private readonly bool $singleUse,
        private readonly int $destinationAccountId,
    ) {
    }

    public function key(): string
    {
        return 'bnb';
    }

    public function generate(QrPaymentRequest $request): QrPaymentResult
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withToken($token)
            ->post($this->url('QRSimple.API/api/v1/main/getQRWithImageAsync'), [
                'currency' => $request->currency ?: $this->currency,
                'gloss' => $request->description,
                'amount' => round($request->amount, 2),
                'singleUse' => $request->singleUse,
                // BNB expira a nivel de día; el vencimiento real a 5 min lo hacemos cumplir
                // nosotros vía Recibo::expires_at + el comando pagos:expirar-vencidos (igual que SIP).
                'expirationDate' => $request->expiresAt->format('Y-m-d'),
                // Puente de reconciliación: nuestro alias viaja acá y vuelve en la notificación.
                'additionalData' => $request->alias,
                'destinationAccountId' => (string) $this->destinationAccountId,
            ]));

        $body = $this->decode($response, 'generar QR');

        return new QrPaymentResult(
            qrImageBase64: (string) ($body['qr'] ?? ''),
            providerQrId: (string) ($body['id'] ?? ''),
            // BNB no entrega un id de transacción al generar (el voucherId recién llega al pagar).
            providerTransactionId: '',
            expiresAt: $request->expiresAt,
            destinationBank: 'BNB',
            destinationAccount: (string) $this->destinationAccountId,
        );
    }

    public function disable(string $alias): void
    {
        $qrId = $this->providerQrId($alias);

        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withToken($token)
            ->post($this->url('QRSimple.API/api/v1/main/CancelQRByIdAsync'), [
                'qrId' => $qrId,
            ]));

        $this->decode($response, 'cancelar QR');
    }

    public function status(string $alias): QrPaymentStatusResult
    {
        $qrId = $this->providerQrId($alias);

        $response = $this->requestWithAuth(fn (PendingRequest $http, string $token) => $http
            ->withToken($token)
            ->post($this->url('QRSimple.API/api/v1/main/getQRStatusAsync'), [
                'qrId' => $qrId,
            ]));

        $body = $this->decode($response, 'consultar estado');

        $voucherId = (string) ($body['voucherId'] ?? '');

        return new QrPaymentStatusResult(
            alias: $alias,
            status: $this->mapStatus((int) ($body['statusId'] ?? 1)),
            // El estado no trae fecha de pago; el voucherId es lo más cercano a un nº de orden.
            providerOrderNumber: $voucherId !== '' && $voucherId !== '0' ? $voucherId : null,
            providerQrId: (string) ($body['id'] ?? $qrId),
        );
    }

    /**
     * 1=No usado; 2=Usado (pagado); 3=Expirado; 4=Con error. (Ver getQRStatusAsync en el PDF.)
     */
    private function mapStatus(int $statusId): PaymentStatus
    {
        return match ($statusId) {
            2 => PaymentStatus::Pagado,
            3 => PaymentStatus::Expirado,
            4 => PaymentStatus::Error,
            default => PaymentStatus::Pendiente,
        };
    }

    /**
     * Traduce nuestro alias al id numérico del QR que asignó el BNB (guardado al generar). Sin
     * ese id, BNB no sabe de qué QR se habla.
     */
    private function providerQrId(string $alias): string
    {
        $qrId = Recibo::where('alias', $alias)->value('provider_qr_id');

        if (blank($qrId)) {
            throw QrPaymentException::requestFailed($this->key(), 'resolver QR', "No hay provider_qr_id guardado para el alias {$alias}.");
        }

        return (string) $qrId;
    }

    /**
     * Ejecuta $callback con un token válido; ante 401 (token vencido/inválido) limpia el cache
     * y reintenta una sola vez con uno nuevo. Mismo patrón que SipQrProvider.
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
            $response = $this->http()->post($this->url('ClientAuthentication.API/api/v1/auth/token'), [
                'accountId' => $this->accountId,
                'authorizationId' => $this->authorizationId,
            ]);

            if ($response->failed()) {
                throw QrPaymentException::authenticationFailed($this->key(), "HTTP {$response->status()}: {$response->body()}");
            }

            $body = $response->json();

            if (! is_array($body) || ($body['success'] ?? false) !== true || blank($body['message'] ?? null)) {
                $mensaje = is_array($body) ? ($body['message'] ?? 'respuesta sin token') : $response->body();

                throw QrPaymentException::authenticationFailed($this->key(), (string) $mensaje);
            }

            // El token del BNB viene en `message` (así lo define el servicio de auth, ver PDF).
            return (string) $body['message'];
        });
    }

    private function url(string $path): string
    {
        return "{$this->baseUrl}/{$path}";
    }

    private function http(): PendingRequest
    {
        return Http::asJson()->acceptJson()->timeout(20);
    }

    /**
     * BNB responde siempre con {success, message, ...}; un `success` falso trae el motivo en
     * `message`. Se normaliza a QrPaymentException para el resto de la app.
     *
     * @return array<string, mixed>
     */
    private function decode(Response $response, string $operation): array
    {
        if ($response->failed()) {
            throw QrPaymentException::requestFailed($this->key(), $operation, "HTTP {$response->status()}: {$response->body()}");
        }

        $body = $response->json();

        if (! is_array($body) || ($body['success'] ?? false) !== true) {
            $mensaje = is_array($body) ? ($body['message'] ?? 'respuesta inesperada') : $response->body();

            throw QrPaymentException::requestFailed($this->key(), $operation, (string) $mensaje);
        }

        return $body;
    }
}
