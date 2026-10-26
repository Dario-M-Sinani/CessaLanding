<?php

namespace Tests\Feature;

use App\Livewire\FilePickerBrowser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Subir una imagen de Comunicados AETN en prod daba 500 (2026-10-26): el picker escribía el
 * .webp con imagewebp() directo a "comunicados-aetn", carpeta que en prod no existía, y
 * imagewebp() no crea carpetas. Ahora guarda primero el original (storeAs sí las crea) y
 * convierte después con ImagenWebp. Sin BD: solo el disco público falso.
 */
class FilePickerSubidaTest extends TestCase
{
    public function test_subir_una_imagen_a_una_carpeta_que_no_existe_la_crea_y_la_convierte_a_webp(): void
    {
        Storage::fake('public');

        Livewire::test(FilePickerBrowser::class, ['path' => 'carpeta-nueva'])
            ->set('nuevoArchivo', UploadedFile::fake()->image('comunicado-08-2026.png', 300, 200))
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists('carpeta-nueva/comunicado-08-2026.webp');
        Storage::disk('public')->assertMissing('carpeta-nueva/comunicado-08-2026.png');
    }

    public function test_un_pdf_se_guarda_tal_cual(): void
    {
        Storage::fake('public');

        Livewire::test(FilePickerBrowser::class, ['path' => 'carpeta-nueva'])
            ->set('nuevoArchivo', UploadedFile::fake()->create('comunicado.pdf', 50, 'application/pdf'))
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists('carpeta-nueva/comunicado.pdf');
    }
}
