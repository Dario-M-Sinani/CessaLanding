<?php

namespace Tests\Feature;

use App\Models\CessaRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Nueva Conexión: tipo de servicio Bifásico (2026-10-13). Antes solo se aceptaban MONOPHASE /
 * TRIPHASIC (igual que el legacy). SQLite en memoria con solo las migraciones de `requests`.
 */
class NuevaConexionPhaseTypeTest extends TestCase
{
    private const MIGRACIONES = [
        '2026_07_24_000001_create_requests_table.php',
        '2026_07_30_000001_add_phase_type_to_requests_table.php',
        '2026_07_30_000002_widen_last_meter_reading_on_requests_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => array_map(fn ($m) => "database/migrations/{$m}", self::MIGRACIONES),
        ]);
        Storage::fake('public');
    }

    private function solicitud(array $over = []): array
    {
        return array_merge([
            'fullname' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'area' => 'URBAN',
            'consumer_type' => 'RESIDENTIAL',
            'phase_type' => 'BIPHASIC',
            'document_number' => '1234567',
            'url_document_front' => UploadedFile::fake()->image('front.jpg'),
            'url_document_back' => UploadedFile::fake()->image('back.jpg'),
            'mobile_phone' => '70000000',
            'address' => 'Calle Falsa 123',
            'zone' => 'Centro',
            'service_type' => 'NUEVO_SUMINISTRO',
        ], $over);
    }

    public function test_bifasico_se_acepta_y_se_guarda(): void
    {
        $this->post('/solicitudes', $this->solicitud())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('BIPHASIC', CessaRequest::sole()->phase_type);
    }

    public function test_tipo_de_servicio_desconocido_se_rechaza(): void
    {
        $this->post('/solicitudes', $this->solicitud(['phase_type' => 'HEXAPHASIC']))
            ->assertSessionHasErrors('phase_type');

        $this->assertSame(0, CessaRequest::count());
    }

    public function test_monofasico_y_trifasico_siguen_funcionando(): void
    {
        foreach (['MONOPHASE', 'TRIPHASIC'] as $fase) {
            $this->post('/solicitudes', $this->solicitud(['phase_type' => $fase]))->assertSessionHasNoErrors();
        }

        $this->assertSame(['MONOPHASE', 'TRIPHASIC'], CessaRequest::orderBy('id')->pluck('phase_type')->all());
    }
}
