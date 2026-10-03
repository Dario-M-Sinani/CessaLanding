<?php

namespace Tests\Feature\Facturacion;

use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Base de los tests de facturación: SQLite en memoria + gateway falso
 * (Http::fake) -- nunca toca cobranza-cessa, api-cobranzas-bancos ni la BD local real.
 * No usa RefreshDatabase: no todas las migraciones del proyecto corren en SQLite (p. ej. la de
 * `personal`), así que se migran solo las tablas que tocan estos tests.
 */
abstract class FacturacionTestCase extends TestCase
{
    private const MIGRACIONES = [
        '0001_01_01_000000_create_users_table.php',
        '2026_08_08_194952_create_recibos_table.php',
        '2026_08_11_095523_add_nro_cliente_and_widen_expires_at_on_recibos_table.php',
        '2026_08_12_000001_add_descripcion_pago_to_recibos_table.php',
        '2026_08_14_000001_add_facturacion_cobranzas_to_recibos_table.php',
    ];

    protected const GATEWAY = 'http://gateway.test';

    protected const PDF_FALSO = '%PDF-1.4 comprobante de prueba';

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => array_map(fn ($m) => "database/migrations/{$m}", self::MIGRACIONES),
        ]);

        config([
            'services.cobranzas.enabled' => true,
            'services.cobranzas.gateway_base_url' => self::GATEWAY,
            'services.cobranzas.gateway_api_key' => 'clave-de-prueba',
        ]);

        Storage::fake('public');
        Http::preventStrayRequests();
    }

    protected function crearRecibo(array $atributos = []): Recibo
    {
        static $secuencia = 0;
        $secuencia++;

        return Recibo::create(array_merge([
            'provider' => 'bisa',
            'alias' => "TEST-FACT-{$secuencia}",
            'nro_cliente' => '179185',
            'amount' => 150.50,
            'currency' => 'BOB',
            'glosa' => 'Pago de prueba',
            'status' => PaymentStatus::Pagado,
            'expires_at' => now()->addDay(),
            'paid_at' => now()->subMinutes(5),
            'provider_order_number' => 'ORD-123',
            'debt_items' => [[
                'nro_cliente' => '179185',
                'anio' => 2026,
                'mes' => 1,
                'importe' => 150.50,
                'nro_comprobante' => '0001',
            ]],
        ], $atributos));
    }

    /**
     * Gateway que factura bien: liquidar -> FACTURADO, comprobante -> PDF.
     */
    protected function fakeGatewayFacturaOk(string $uuid = '11111111-2222-3333-4444-555555555555'): void
    {
        Http::fake([
            self::GATEWAY.'/api/externo/recibos-web/liquidar/' => Http::response([
                'estado' => 'FACTURADO',
                'cobranzas_uuid' => $uuid,
                'error' => '',
            ]),
            self::GATEWAY.'/api/externo/recibos-web/*/comprobante/' => Http::response(self::PDF_FALSO, 200, ['Content-Type' => 'application/pdf']),
        ]);
    }
}
