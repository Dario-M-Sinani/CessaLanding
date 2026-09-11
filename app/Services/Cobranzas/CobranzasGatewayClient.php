<?php

namespace App\Services\Cobranzas;

use App\Services\Cobranzas\Exceptions\CobranzasException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Cliente hacia el gateway de facturación de `cobranza_cessa` (apps/facturacion_externa) --
 * ese backend corre en la red interna de CESSA (10.1.1.88, junto a api-cobranzas-bancos) y hace
 * ahí la parte que cessa-laravel nunca pudo hacer directo desde Hostinger (sin ruta hacia
 * 10.1.1.x): autenticar contra api-cobranzas-bancos, abrir Caja, crear/pagar la Transacción y
 * bajar el comprobante. Acá solo se manda "esto ya se cobró, liquidalo" con el detalle crudo de
 * deuda -- la transformación de formato (fechas, importe) y el armado del "documento" los hace
 * el gateway (ver services/cobranzas_banco_client.py de ese repo).
 *
 * Reemplaza a la vieja CobranzasBancoService (llamaba directo a api-cobranzas-bancos, retirada
 * porque esa ruta nunca fue alcanzable desde este hosting).
 */
class CobranzasGatewayClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {
    }

    /**
     * Pide al gateway que liquide un Recibo ya pagado. Idempotente del lado del gateway por
     * `alias`: reenviar el mismo aviso (reintento de red, botón "Reintentar Facturación") nunca
     * paga dos veces.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed> cuerpo decodificado (estado, cobranzas_uuid, error, ...)
     */
    public function liquidar(array $payload): array
    {
        $response = $this->http()->post("{$this->baseUrl}/api/externo/recibos-web/liquidar/", $payload);

        // 200 = quedó Facturado; 502 = el gateway sí procesó el aviso pero api-cobranzas-bancos
        // rechazó el pago -- en ambos casos el cuerpo trae el estado real, hay que leerlo. Otro
        // código (403 api key mal puesta, 400 payload inválido, 500 inesperado) es un error de
        // nuestro lado, no de negocio.
        if (! in_array($response->status(), [200, 502], true)) {
            throw CobranzasException::requestFailed('liquidar recibo', "HTTP {$response->status()}: {$response->body()}");
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw CobranzasException::requestFailed('liquidar recibo', "respuesta no-JSON: {$response->body()}");
        }

        return $data;
    }

    /**
     * @return string bytes crudos del PDF
     */
    public function obtenerComprobantePdf(string $alias): string
    {
        $response = $this->http()->get("{$this->baseUrl}/api/externo/recibos-web/{$alias}/comprobante/");

        if ($response->failed()) {
            throw CobranzasException::requestFailed('obtener comprobante', "HTTP {$response->status()}: {$response->body()}");
        }

        return $response->body();
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerComprobanteJson(string $alias): array
    {
        $response = $this->http()->get("{$this->baseUrl}/api/externo/recibos-web/{$alias}/comprobante-json/");

        if ($response->failed()) {
            throw CobranzasException::requestFailed('obtener comprobante JSON', "HTTP {$response->status()}: {$response->body()}");
        }

        return (array) $response->json();
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders(['X-Api-Key' => $this->apiKey])->acceptJson()->timeout(30);
    }
}
