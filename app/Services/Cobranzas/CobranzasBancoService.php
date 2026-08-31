<?php

namespace App\Services\Cobranzas;

use App\Services\Cobranzas\Exceptions\CobranzasException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Cliente de api-cobranzas-bancos -- la API Lumen/Passport que expone el sistema comercial de
 * CESSA (SIIC) para registrar cobros como facturas reales (ver
 * NECESIDADES_SIIC_FACTURACION.md y documentacion/PLAN_PAGO_Y_FACTURACION.md). Sigue el mismo
 * patrón que SipQrProvider: token cacheado, una excepción propia, sin desactivar TLS nunca.
 *
 * Flujo: autenticar (OAuth2 password grant) -> verificar/abrir Caja del día -> crear Transacción
 * -> pagar Transacción (detalle de deuda + documento) -> obtener Comprobante en PDF.
 *
 * El formato exacto de `detalle`/`documento` para "pagar-otro-documento" se confirmó
 * decompilando (javap) el .class real de la app "Cobranza" (Spring Boot/JSF) que ya usa esta
 * API en producción -- no adivinado. El único vacío real que queda es de negocio, no de
 * formato: qué `ente_id`/`banco_id` (catálogos `GET /v1/entes` y `GET /v1/bancos`) corresponden
 * a "pago por QR/transferencia electrónica" y "Banco BISA" -- ver
 * FacturacionRecibo::construirDocumento() y config/services.php. Nada de esto se pudo probar en
 * vivo todavía (no hay forma de llegar a la red interna de CESSA desde este entorno).
 */
class CobranzasBancoService
{
    private const TOKEN_CACHE_KEY = 'cobranzas:auth_token';

    private const TOKEN_TTL_SECONDS = 55 * 60; // el token Passport real dura ~1h; se cachea un poco menos por margen.

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $username,
        private readonly string $password,
        private readonly string $agenciaSigla,
    ) {
    }

    /**
     * Verifica que exista una Caja abierta hoy para nuestro usuario; si no existe, la abre.
     * api-cobranzas-bancos ata la Caja al usuario+día calendario (ver CajaController::aperturar
     * del lado de esa API) -- no hace falta reabrir si ya está abierta.
     */
    public function asegurarCajaAbierta(): void
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http) => $http
            ->get("{$this->baseUrl}/v1/cajas/existe"));

        if ($response->successful()) {
            return;
        }

        if ($response->status() !== 404) {
            throw CobranzasException::requestFailed('verificar caja', "HTTP {$response->status()}: {$response->body()}");
        }

        $response = $this->requestWithAuth(fn (PendingRequest $http) => $http
            ->post("{$this->baseUrl}/v1/cajas/aperturar", [
                'agencia_sigla' => $this->agenciaSigla,
                'monto_inicial' => 0,
                'descripcion_apertura' => 'Apertura automática -- sitio web CESSA (cobros por QR)',
            ]));

        if ($response->failed()) {
            throw CobranzasException::requestFailed('aperturar caja', $this->extractErrorMessage($response));
        }
    }

    /**
     * Crea una Transacción vacía (estado CREADA) y devuelve su uuid.
     */
    public function crearTransaccion(): string
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http) => $http
            ->post("{$this->baseUrl}/v1/transacciones"));

        if ($response->failed()) {
            throw CobranzasException::requestFailed('crear transacción', $this->extractErrorMessage($response));
        }

        $uuid = $response->json('uuid');

        if (! $uuid) {
            throw CobranzasException::requestFailed('crear transacción', 'la respuesta no trajo uuid: '.$response->body());
        }

        return $uuid;
    }

    /**
     * Paga una Transacción ya creada con el detalle exacto de deuda (snapshot guardado en
     * Recibo::debt_items al momento de generar el QR, ver PagoQrController) más el "documento"
     * que identifica cómo/con qué banco entró el dinero (ver FacturacionRecibo::construirDocumento).
     *
     * Usa `/pagar-otro-documento`, no `/pagar` a secas -- confirmado decompilando la app
     * "Cobranza" (Spring Boot/JSF) que ya usa esta API en producción: es el ÚNICO endpoint de
     * pago que ese cliente real llama (nunca usa `/pagar`), y es semánticamente el correcto para
     * nuestro caso -- el dinero entró por un canal externo (SIP/Banco BISA), no por caja física.
     *
     * @param  array<int, array<string, mixed>>  $detalle
     * @param  array<string, mixed>  $documento
     */
    public function pagarTransaccion(string $uuid, array $detalle, array $documento): void
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http) => $http
            ->put("{$this->baseUrl}/v1/transacciones/{$uuid}/pagar-otro-documento", [
                'detalle' => $detalle,
                'documento' => $documento,
            ]));

        if ($response->failed()) {
            throw CobranzasException::requestFailed('pagar transacción', $this->extractErrorMessage($response));
        }
    }

    /**
     * Descarga el comprobante/factura en PDF de una Transacción ya pagada.
     *
     * @return string bytes crudos del PDF
     */
    public function obtenerComprobantePdf(string $uuid): string
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http) => $http
            ->get("{$this->baseUrl}/v1/transacciones/{$uuid}/documentos", ['formato' => 'pdf']));

        if ($response->failed()) {
            throw CobranzasException::requestFailed('obtener comprobante', $this->extractErrorMessage($response));
        }

        return $response->body();
    }

    /**
     * Obtiene los datos estructurados en JSON del comprobante/factura de una Transacción ya pagada.
     *
     * @return array<string, mixed>
     */
    public function obtenerComprobanteJson(string $uuid): array
    {
        $response = $this->requestWithAuth(fn (PendingRequest $http) => $http
            ->get("{$this->baseUrl}/v1/transacciones/{$uuid}/documentos", ['formato' => 'json']));

        if ($response->failed()) {
            throw CobranzasException::requestFailed('obtener comprobante JSON', $this->extractErrorMessage($response));
        }

        $data = $response->json();
        if (isset($data[0]) && is_array($data[0])) {
            return $data[0];
        }

        return (array) $data;
    }

    /**
     * Ejecuta $callback con un token válido; si la API responde 401 (token vencido/inválido),
     * limpia el cache y reintenta una sola vez con un token nuevo. Mismo patrón que
     * SipQrProvider::requestWithAuth().
     */
    private function requestWithAuth(callable $callback): Response
    {
        $token = $this->getToken();
        $response = $callback($this->http()->withToken($token));

        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $token = $this->getToken(forceRefresh: true);
            $response = $callback($this->http()->withToken($token));
        }

        return $response;
    }

    private function getToken(bool $forceRefresh = false): string
    {
        if ($forceRefresh) {
            Cache::forget(self::TOKEN_CACHE_KEY);
        }

        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_TTL_SECONDS, function () {
            $response = $this->http()->post("{$this->baseUrl}/oauth/token", [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password,
                'scope' => '',
            ]);

            if ($response->failed()) {
                throw CobranzasException::authenticationFailed("HTTP {$response->status()}: {$response->body()}");
            }

            $token = $response->json('access_token');

            if (! $token) {
                throw CobranzasException::authenticationFailed('la respuesta no trajo access_token: '.$response->body());
            }

            return $token;
        });
    }

    private function extractErrorMessage(Response $response): string
    {
        $error = $response->json('error');

        if (is_array($error)) {
            return json_encode($error, JSON_UNESCAPED_UNICODE);
        }

        return (string) ($error ?? $response->body());
    }

    private function http(): PendingRequest
    {
        return Http::asJson()->acceptJson()->timeout(30);
    }
}
