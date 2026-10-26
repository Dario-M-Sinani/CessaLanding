<?php

namespace App\Livewire;

use App\Filament\Support\ImagenWebp;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilePickerBrowser extends Component
{
    use WithFileUploads;

    public string $path = '';

    public $nuevoArchivo = null;

    protected function disk()
    {
        return Storage::disk('public');
    }

    protected function normalizePath(string $path): string
    {
        return trim(str_replace(['..', '\\'], '', $path), '/');
    }

    public function mount(string $path = ''): void
    {
        $this->path = $this->normalizePath($path);
    }

    public function goTo(string $path): void
    {
        $this->path = $this->normalizePath($path);
    }

    public function breadcrumbs(): array
    {
        if ($this->path === '') {
            return [];
        }

        $acc = '';
        $crumbs = [];

        foreach (explode('/', $this->path) as $part) {
            $acc = trim($acc . '/' . $part, '/');
            $crumbs[] = ['label' => $part, 'path' => $acc];
        }

        return $crumbs;
    }

    public function folders()
    {
        return collect($this->disk()->directories($this->path))
            ->map(fn ($dir) => basename($dir))
            ->sort()
            ->values();
    }

    public function files()
    {
        return collect($this->disk()->files($this->path))
            ->reject(fn ($file) => str_starts_with(basename($file), '.'))
            ->map(fn ($file) => [
                'name' => basename($file),
                'path' => $file,
                'url' => $this->disk()->url($file),
                'ext' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
            ])
            ->sortBy('name')
            ->values();
    }

    public function updatedNuevoArchivo(): void
    {
        // Whitelist: este picker sirve logos de banco, imágenes de noticias/contenidos/galería,
        // el PDF del Documento PEI y los documentos institucionales (pdf/zip, hasta 100MB,
        // mismos tipos y límite que DocumentResource::url) -- nunca debe aceptar tipos
        // arbitrarios (HTML/SVG con script embebido, ejecutables, etc.).
        $this->validate([
            'nuevoArchivo' => 'file|max:102400|mimes:jpg,jpeg,png,webp,pdf,zip',
        ]);

        $guardado = $this->storeConvertingImagesToWebp($this->nuevoArchivo, $this->path);

        $this->nuevoArchivo = null;

        // Lo recién subido queda elegido solo (mismo evento que un click en la grilla): el
        // modal del formulario escribe el valor en el campo y se cierra.
        $this->dispatch('archivo-seleccionado', path: $guardado, url: $this->disk()->url($guardado));
    }

    /**
     * Guarda primero el original (storeAs crea la carpeta si no existe) y después convierte
     * jpg/jpeg/png a WebP -- ver ImagenWebp.
     */
    public static function storeConvertingImagesToWebp($uploadedFile, string $path): string
    {
        $stored = $uploadedFile->storeAs($path, $uploadedFile->getClientOriginalName(), 'public');

        return ImagenWebp::convertir(Storage::disk('public'), $stored);
    }

    public function render()
    {
        return view('livewire.file-picker-browser');
    }
}
