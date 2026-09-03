<?php

namespace Tests\Feature;

use App\Filament\Resources\PublicationResource\Pages\EditPublication;
use App\Models\Document;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * PublicationResource no tenía ningún campo para adjuntar documentos, a pesar de que el
 * modelo Publication ya tiene la relación hasMany(Document) y /procesos ya sabe renderizarlos
 * (2026-09-03). Verifica que el Repeater agregado sí crea el Document real vía la relación.
 * No usa RefreshDatabase a propósito -- corre contra la BD local real, restaura el estado al final.
 */
class PublicationDocumentsFormTest extends TestCase
{
    public function test_adding_a_document_via_the_repeater_creates_a_real_document_record(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@cessa.com.bo')->first();
        $this->assertNotNull($admin, 'Falta el usuario admin@cessa.com.bo en la BD local.');

        $publication = Publication::create([
            'title' => 'TEST-DOC-REPEATER',
            'description' => 'Publicación temporal de prueba automática',
            'type' => 'BIDDING',
            'published' => 'S',
        ]);

        $this->actingAs($admin);

        $file = UploadedFile::fake()->create('pliego-de-condiciones.pdf', 100, 'application/pdf');

        $test = Livewire::test(EditPublication::class, ['record' => $publication->getRouteKey()]);

        $test->fillForm([
            'documents' => [
                'nuevo-item' => [
                    'url' => [$file],
                    'title' => 'Pliego de Condiciones',
                ],
            ],
        ]);
        $test->call('save');
        $test->assertHasNoFormErrors();

        $document = Document::where('publication_id', $publication->id)->first();

        $this->assertNotNull($document, 'No se creó ningún Document para la Publication.');
        $this->assertSame('Pliego de Condiciones', $document->title);
        $this->assertStringStartsWith('documentos/procesos/', $document->url);
        Storage::disk('public')->assertExists($document->url);

        $publication->documents()->delete();
        $publication->delete();
    }

    /**
     * FileManagerAction ("Elegir del Gestor de Archivos") escribía el valor con
     * data_set($livewire, "data.{$targetField}", ...) -- siempre relativo a la RAÍZ del
     * formulario, así que dentro de un item de Repeater apuntaba al lugar equivocado (ver
     * ESTADO_SEGURIDAD_MIGRACION.md §-1octies, por eso nunca se había usado ahí). Corregido
     * usando $set/$get (relativos al contenedor real del componente al que está atada la
     * acción) en vez de armar la ruta a mano. Verifica que, dentro del Repeater de
     * Publicaciones, elegir un archivo ya existente del Gestor escribe la ruta en el item
     * correcto del Repeater, no en la raíz del formulario.
     */
    public function test_choosing_a_file_from_gestor_de_archivos_writes_to_the_correct_repeater_item(): void
    {
        Storage::fake('public');

        $existingPath = 'documentos/procesos/ya-existente-en-el-gestor.pdf';
        Storage::disk('public')->put($existingPath, 'contenido de prueba');

        $admin = User::where('email', 'admin@cessa.com.bo')->first();
        $this->assertNotNull($admin, 'Falta el usuario admin@cessa.com.bo en la BD local.');

        $publication = Publication::create([
            'title' => 'TEST-DOC-PICKER',
            'type' => 'BIDDING',
            'published' => 'S',
        ]);

        $this->actingAs($admin);

        $test = Livewire::test(EditPublication::class, ['record' => $publication->getRouteKey()]);

        // Simula un item del Repeater ya con Título cargado pero sin archivo todavía --
        // el escenario real es elegir el archivo del Gestor en vez de subirlo de nuevo.
        $test->fillForm(['documents' => ['item-de-prueba' => ['title' => 'Bases del Proceso']]]);

        $test->callFormComponentAction(
            'documents.item-de-prueba.url',
            'elegirArchivo_url',
            arguments: ['path' => $existingPath],
        );

        // FileUpload guarda su estado en vivo como array (incluso sin multiple()) -- se
        // aplana a un string plano recién al guardar, vía dehydrateStateUsing().
        $test->assertSet('data.documents.item-de-prueba.url', [$existingPath]);

        $test->call('save');
        $test->assertHasNoFormErrors();

        $document = Document::where('publication_id', $publication->id)->first();

        $this->assertNotNull($document, 'No se creó ningún Document para la Publication.');
        $this->assertSame($existingPath, $document->url);

        $publication->documents()->delete();
        $publication->delete();
    }
}
