<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource\Pages\EditContent;
use App\Models\Content;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regresión del bug real encontrado el 2026-08-31: el Repeater "Personal Masculino / Femenino"
 * de ContentResource usaba TextInput::numeric() (solo valida, no castea) en vez de ->integer()
 * -- un valor cargado por el panel se guardaba como string ("39") en vez de int, y
 * `resources/js/Pages/LaCompania/Rrhh.vue` sumaba esos valores con "+", que en JS concatena
 * strings en vez de sumar ("161" + "39" = "16139"), rompiendo los porcentajes del gráfico de
 * torta. No usa RefreshDatabase a propósito -- corre contra la BD local real (mismo patrón que
 * el resto de la app), así que restaura el estado original del Content al final.
 */
class RrhhGenderStatsFormTest extends TestCase
{
    private ?array $originalGenderStats = null;

    protected function setUp(): void
    {
        parent::setUp();

        $content = Content::where('alias', 'recursos-humanos')->first();
        $this->originalGenderStats = $content?->gender_yearly_stats;
    }

    protected function tearDown(): void
    {
        if ($this->originalGenderStats !== null) {
            Content::where('alias', 'recursos-humanos')->first()
                ?->forceFill(['gender_yearly_stats' => $this->originalGenderStats])
                ->save();
        }

        parent::tearDown();
    }

    public function test_editing_gender_yearly_stats_via_the_panel_saves_integers_not_strings(): void
    {
        $admin = User::where('email', 'admin@cessa.com.bo')->first();
        $content = Content::where('alias', 'recursos-humanos')->first();

        $this->assertNotNull($admin, 'Falta el usuario admin@cessa.com.bo en la BD local.');
        $this->assertNotNull($content, 'Falta el Content alias=recursos-humanos en la BD local.');

        $this->actingAs($admin);

        $test = Livewire::test(EditContent::class, ['record' => $content->getRouteKey()]);

        // Estado real del Repeater (claves UUID por fila) tal como lo carga el formulario.
        $genderRows = $test->get('data.gender_yearly_stats');
        $this->assertNotEmpty($genderRows, 'El Repeater gender_yearly_stats llegó vacío al formulario.');

        $originalRowCount = count($genderRows);

        // "Borra años": usa la action real "delete" del Repeater (no un unset() manual -- fillForm()
        // solo hace data_set() por clave, nunca borra una rama entera que falte en el nuevo estado).
        $oldestKey = collect($genderRows)->sortBy('year')->keys()->first();
        $test->callFormComponentAction('gender_yearly_stats', 'delete', arguments: ['item' => $oldestKey]);

        $genderRows = $test->get('data.gender_yearly_stats');

        // "Cambia datos": todo se manda como STRING, igual que llega un <input> real del navegador.
        foreach ($genderRows as $key => &$row) {
            $row['year'] = (string) $row['year'];
            $row['male'] = (string) $row['male'];
            $row['female'] = (string) ((int) $row['female'] + 1);
        }
        unset($row);

        $test->fillForm(['gender_yearly_stats' => $genderRows]);
        $test->call('save');
        $test->assertHasNoFormErrors();

        $fresh = $content->fresh();

        $this->assertCount($originalRowCount - 1, $fresh->gender_yearly_stats, 'La fila borrada debería haber desaparecido.');

        foreach ($fresh->gender_yearly_stats as $row) {
            $this->assertIsInt($row['year'], "year del año {$row['year']} se guardó como ".gettype($row['year']).", no int.");
            $this->assertIsInt($row['male'], "male del año {$row['year']} se guardó como ".gettype($row['male']).", no int.");
            $this->assertIsInt($row['female'], "female del año {$row['year']} se guardó como ".gettype($row['female']).", no int.");
        }
    }
}
