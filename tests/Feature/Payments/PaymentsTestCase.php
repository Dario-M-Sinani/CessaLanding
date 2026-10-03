<?php

namespace Tests\Feature\Payments;

use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Base de los tests de pagos QR (BNB): SQLite en memoria (solo las tablas que tocan) + gateway
 * BNB falso (Http::fake). Nunca toca test.bnb.com.bo ni la BD local real. Mismo enfoque que
 * Tests\Feature\Facturacion\FacturacionTestCase (no usa RefreshDatabase porque no todas las
 * migraciones corren en SQLite).
 */
abstract class PaymentsTestCase extends TestCase
{
    private const MIGRACIONES = [
        '0001_01_01_000000_create_users_table.php',
        '2026_08_08_194952_create_recibos_table.php',
        '2026_08_11_095523_add_nro_cliente_and_widen_expires_at_on_recibos_table.php',
        '2026_08_12_000001_add_descripcion_pago_to_recibos_table.php',
        '2026_08_14_000001_add_facturacion_cobranzas_to_recibos_table.php',
    ];

    protected const BNB = 'http://bnb.test';

    protected const TOKEN = 'TOKEN-DE-PRUEBA';

    protected const QR_PNG_BASE64 = 'aVZCT1J3MEtHZ29BQUFBTlNVaEVVZw=='; // bytes cualquiera

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => array_map(fn ($m) => "database/migrations/{$m}", self::MIGRACIONES),
        ]);

        config([
            // SIIC falso para los tests que pegan al endpoint de generación (consulta de deuda).
            'services.cessa_api.url' => 'http://siic.test',
            'services.cessa_api.token' => 'token-de-prueba',
            // Credenciales del Basic Auth del callback de SIP.
            'services.sip.callback_username' => 'sip-user',
            'services.sip.callback_password' => 'sip-pass',
            'services.bnb.base_url' => self::BNB,
            'services.bnb.uri_subfolder' => '',
            'services.bnb.account_id' => 'ACC==',
            'services.bnb.authorization_id' => 'AUTH==',
            'services.bnb.currency' => 'BOB',
            'services.bnb.single_use' => true,
            'services.bnb.destination_account_id' => 1,
        ]);

        Http::preventStrayRequests();
    }

    protected function crearReciboBnb(array $atributos = []): Recibo
    {
        static $secuencia = 0;
        $secuencia++;

        return Recibo::create(array_merge([
            'provider' => 'bnb',
            'alias' => "CESSA-WEB-BNB-{$secuencia}",
            'nro_cliente' => '197596',
            'amount' => 100.00,
            'currency' => 'BOB',
            'glosa' => 'Pago de prueba',
            'status' => PaymentStatus::Pendiente,
            'expires_at' => now()->addMinutes(5),
            'provider_qr_id' => (string) (900000 + $secuencia),
        ], $atributos));
    }

    protected function urlToken(): string
    {
        return self::BNB.'/ClientAuthentication.API/api/v1/auth/token';
    }

    protected function urlGenerar(): string
    {
        return self::BNB.'/QRSimple.API/api/v1/main/getQRWithImageAsync';
    }

    protected function urlEstado(): string
    {
        return self::BNB.'/QRSimple.API/api/v1/main/getQRStatusAsync';
    }

    protected function urlCancelar(): string
    {
        return self::BNB.'/QRSimple.API/api/v1/main/CancelQRByIdAsync';
    }

    /** Token siempre válido. */
    protected function fakeToken(): array
    {
        return [$this->urlToken() => Http::response(['success' => true, 'message' => self::TOKEN])];
    }
}
