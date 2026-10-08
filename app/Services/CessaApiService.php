<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CessaApiService
{
    protected string $baseUrl;
    protected string $authorizationToken;

    public function __construct()
    {
        $this->baseUrl = config('services.cessa_api.url', env('CESSA_API_URL', ''));
        $this->authorizationToken = config('services.cessa_api.token', env('CESSA_API_TOKEN', ''));
    }

    /**
     * Get HTTP client with pre-configured headers
     */
    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => $this->authorizationToken,
            ])
            // throw: false — a 404/422/500 from the SIIC API is a normal "not found" / validation
            // response we want to inspect (it returns {"error": "...", "code": ...}), not a connection failure.
            ->retry(3, 100, throw: false);
    }

    public function getPeriods(string $id = ''): array
    {
        $path = '/v1/periodos-tarifas' . ($id ? '/' . $id : '');

        return Cache::remember('cessa_periods' . ($id ? ":{$id}" : ''), 3600, function () use ($path) {
            return $this->fixEncoding($this->client()->get($path)->json());
        });
    }

    public function getCategories(): array
    {
        return Cache::remember('cessa_categories', 3600, function () {
            return $this->fixEncoding($this->client()->get('/v1/categorias')->json());
        });
    }

    public function consultaDeuda(array $params = []): array
    {
        if (config('services.cobranzas.deuda_via_gateway')) {
            return $this->consultaDeudaViaGateway($params);
        }

        return $this->fixEncoding($this->client()->get('/v1/consulta/cliente', $params)->json());
    }

    /**
     * Deuda leída por el gateway (cobranza-cessa), que la pide "como banco" a
     * api-cobranzas-bancos -- el mismo SIIC donde después se paga el QR. Si se lee
     * de un SIIC (ej. prod) y se paga en otro (test), el pago falla con "La deuda no
     * existe con los datos proporcionados" (pasó el 2026-10-06 con el cliente 115997).
     * Mismos parámetros y misma respuesta que /v1/consulta/cliente del SIIC.
     */
    protected function consultaDeudaViaGateway(array $params): array
    {
        $response = $this->gateway()
            ->timeout(30)
            ->retry(2, 200, throw: false)
            ->get('/api/externo/consulta/cliente/', $params);

        $data = $response->json();

        // 404 con {"error": ...} es "no existe el abonado" (lo resuelven los controllers).
        // 403 (API key), 503 (api-cobranzas/SIIC caídos) o una página HTML de Cloudflare
        // son fallas de conexión: excepción, para que los controllers muestren "no se
        // pudo conectar" en vez de "no se encontró ningún abonado".
        if (!is_array($data) || $response->status() === 403 || $response->serverError()) {
            throw new \RuntimeException('Gateway de cobranzas: HTTP ' . $response->status() . ' al consultar deuda');
        }

        return $this->fixEncoding($data);
    }

    /** Cliente HTTP hacia el gateway (cobranza-cessa), autenticado con la API key compartida. */
    protected function gateway()
    {
        return Http::baseUrl(rtrim((string) config('services.cobranzas.gateway_base_url'), '/'))
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Api-Key' => (string) config('services.cobranzas.gateway_api_key'),
            ]);
    }

    public function calculoConsumo(array $params = []): array
    {
        return $this->fixEncoding($this->client()->get('/v1/consulta/calculo-consumo', $params)->json());
    }

    public function buscarTramite(array $params = []): array
    {
        return $this->fixEncoding($this->client()->get('/v1/consulta/tramite', $params)->json());
    }

    /**
     * Últimos comprobantes ya pagados del cliente, por cualquier canal (GET /v1/clientes/{c}/pagos),
     * del más reciente al más viejo. Cada ítem trae la clave del comprobante (la que pide
     * comprobantePdf()) + detalle, importe y fecha/hora del pago.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ultimosPagos(string $nroCliente, int $limit = 12): array
    {
        // Con la deuda vía gateway, las facturas también: así las pagadas por QR (que se facturan
        // en el SIIC del gateway) aparecen en "Tus últimas facturas" del mismo entorno.
        $response = config('services.cobranzas.deuda_via_gateway')
            ? $this->gateway()->timeout(30)->retry(2, 200, throw: false)->get("/api/externo/consulta/clientes/{$nroCliente}/pagos/", ['limit' => $limit])
            : $this->client()->timeout(30)->get("/v1/clientes/{$nroCliente}/pagos", ['limit' => $limit]);

        if (! $response->successful()) {
            throw new \RuntimeException("SIIC /pagos respondió HTTP {$response->status()}");
        }

        return $this->fixEncoding($response->json('items') ?? []);
    }

    /**
     * PDF real de la factura de un comprobante (POST /v1/comprobantes, formato pdf) -- el mismo
     * que arma el SIIC para cajas. Devuelve null si el SIIC no lo pudo generar.
     *
     * @param  array<string, mixed>  $comprobante  clave del comprobante, tal cual vino de ultimosPagos()
     */
    public function comprobantePdf(array $comprobante): ?string
    {
        $response = config('services.cobranzas.deuda_via_gateway')
            ? $this->gateway()->timeout(60)->withHeaders(['Accept' => 'application/pdf'])
                ->post('/api/externo/consulta/comprobantes/pdf/', ['item' => $comprobante])
            : $this->client()->timeout(60)->withHeaders(['Accept' => 'application/pdf'])
                ->post('/v1/comprobantes', ['formato' => 'pdf', 'items' => [$comprobante]]);

        if (! $response->successful() || ! str_starts_with($response->body(), '%PDF')) {
            return null;
        }

        return $response->body();
    }

    /**
     * The SIIC API's own database has names/addresses stored as double-encoded UTF-8
     * (e.g. "SIÑANI" comes back as "SIÃ\x91ANI") for an unknown subset of records — this
     * is a data quality issue upstream in their system, not something we can fix at the
     * source. We repair it defensively here: round-tripping a double-encoded string
     * through ISO-8859-1 recovers the original UTF-8 bytes. Clean, already-correct UTF-8
     * text produces an invalid byte sequence when we try this, so mb_check_encoding()
     * safely tells us when NOT to apply the "fix" — it never touches correct text.
     */
    protected function fixEncoding($data)
    {
        if (is_array($data)) {
            return array_map(fn ($value) => $this->fixEncoding($value), $data);
        }

        if (!is_string($data) || $data === '') {
            return $data;
        }

        $candidate = @mb_convert_encoding($data, 'ISO-8859-1', 'UTF-8');

        if ($candidate !== false && $candidate !== $data && mb_check_encoding($candidate, 'UTF-8')) {
            return $candidate;
        }

        return $data;
    }
}
