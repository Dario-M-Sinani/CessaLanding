<?php

namespace App\Services\Cobranzas;

use App\Services\Cobranzas\Exceptions\CobranzasException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Cliente del gateway propio (cobranza-cessa, Django en 10.1.1.88 / test01.cessa.com.bo) que
 * factura contra api-cobranzas-bancos por nosotros -- este sitio (Hostinger) no tiene ruta
 * directa a esa API (red interna de CESSA), ver README.md de cobranza-cessa, sección "Gateway
 * para cessa-laravel". Auth servidor-a-servidor con header X-Api-Key, no OAuth: las
 * credenciales de Cajero/Caja y el client_id/secret de api-cobranzas-bancos viven solo del
 * lado del gateway (nunca acá).
 */
class CobranzasGatewayClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {
    }

    /**
     * Envía el snapshot de un Recibo ya Pagado para que el gateway lo liquide contra
     * api-cobranzas-bancos. Idempotente por `alias` del lado del gateway: reenviar el mismo
     * alias no lo vuelve a pagar si ya quedó FACTURADO.
     *
     * @param  array<int, array<string, mixed>>  $detalle  snapshot crudo de SIIC (Recibo::debt_items) -- el gateway arma el "documento" (ente/banco) él mismo.
     * @return array<string, mixed> {alias, estado, cobranzas_uuid, error, intentos, comprobante_disponible, ...}
     */
    public function liquidar(
        string $alias,
        string $nroCliente,
        float $monto,
        string $moneda,
        array $detalle,
        string $fechaPago,
        string $numeroOrdenOriginante = '',
    ): array {
        $response = $this->http()->post("{$this->baseUrl}/api/externo/recibos-web/liquidar/", [
            'alias' => $alias,
            'nro_cliente' => $nroCliente,
            'monto' => $monto,
            'moneda' => $moneda,
            'detalle' => $detalle,
            'fecha_pago' => $fechaPago,
            'numero_orden_originante' => $numeroOrdenOriginante,
        ]);

        // 200 = FACTURADO; 502 = el gateway sí procesó pero SIIC rechazó el pago (rechazo de
        // negocio, no falla de nuestra integración) -- en ambos casos el body ya trae
        // {estado, error, ...} y quien llama decide qué hacer. Cualquier otro código (400
        // payload inválido, 403 API key mal configurada, 500/504 del lado del gateway) sí es
        // un problema de integración nuestro.
        if (! in_array($response->status(), [200, 502], true)) {
            throw CobranzasException::requestFailed('liquidar recibo (gateway)', "HTTP {$response->status()}: ".$this->extractErrorMessage($response));
        }

        return $response->json();
    }

    /**
     * Vuelve a preguntar el estado de una liquidación ya enviada -- útil si el POST de
     * liquidar() se cayó en la respuesta pero sí llegó a procesarse del lado del gateway.
     *
     * @return array<string, mixed>
     */
    public function consultar(string $alias): array
    {
        $response = $this->http()->get("{$this->baseUrl}/api/externo/recibos-web/{$alias}/");

        if ($response->failed()) {
            throw CobranzasException::requestFailed('consultar liquidación (gateway)', $this->extractErrorMessage($response));
        }

        return $response->json();
    }

    /**
     * @return string bytes crudos del PDF
     */
    public function comprobantePdf(string $alias): string
    {
        $response = $this->http()->get("{$this->baseUrl}/api/externo/recibos-web/{$alias}/comprobante/");

        if ($response->failed()) {
            throw CobranzasException::requestFailed('obtener comprobante (gateway)', $this->extractErrorMessage($response));
        }

        return $response->body();
    }

    /**
     * @return array<string, mixed>
     */
    public function comprobanteJson(string $alias): array
    {
        $response = $this->http()->get("{$this->baseUrl}/api/externo/recibos-web/{$alias}/comprobante-json/");

        if ($response->failed()) {
            throw CobranzasException::requestFailed('obtener comprobante JSON (gateway)', $this->extractErrorMessage($response));
        }

        return $response->json();
    }

    private function extractErrorMessage(Response $response): string
    {
        $error = $response->json('error') ?? $response->json('detail');

        if (is_array($error)) {
            return json_encode($error, JSON_UNESCAPED_UNICODE);
        }

        return (string) ($error ?? $response->body());
    }

    private function http(): PendingRequest
    {
        return Http::asJson()->acceptJson()->timeout(30)->withHeaders([
            'X-Api-Key' => $this->apiKey,
        ]);
    }
}
