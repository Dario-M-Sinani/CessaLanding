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
        return $this->fixEncoding($this->client()->get($path)->json());
    }

    public function getCategories(): array
    {
        return Cache::remember('cessa_categories', 3600, function () {
            return $this->fixEncoding($this->client()->get('/v1/categorias')->json());
        });
    }

    public function consultaDeuda(array $params = []): array
    {
        return $this->fixEncoding($this->client()->get('/v1/consulta/cliente', $params)->json());
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
