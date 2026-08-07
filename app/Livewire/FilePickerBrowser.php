<?php

namespace App\Livewire;

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

        $this->storeConvertingImagesToWebp($this->nuevoArchivo, $this->path);

        $this->nuevoArchivo = null;
    }

    /**
     * jpg/jpeg/png se convierten a WebP antes de guardarse (más liviano, misma calidad
     * visible) -- pdf/zip/webp se guardan tal cual, no hay nada que convertir.
     */
    public static function storeConvertingImagesToWebp($uploadedFile, string $path): void
    {
        $ext = strtolower($uploadedFile->getClientOriginalExtension());
        $baseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $uploadedFile->storeAs($path, $uploadedFile->getClientOriginalName(), 'public');

            return;
        }

        $source = $ext === 'png'
            ? imagecreatefrompng($uploadedFile->getRealPath())
            : imagecreatefromjpeg($uploadedFile->getRealPath());

        if ($ext === 'png') {
            imagepalettetotruecolor($source);
            imagealphablending($source, true);
            imagesavealpha($source, true);
        }

        $destRelative = trim($path . '/' . $baseName . '.webp', '/');
        $destAbsolute = Storage::disk('public')->path($destRelative);

        imagewebp($source, $destAbsolute, 82);
        imagedestroy($source);
    }

    public function render()
    {
        return view('livewire.file-picker-browser');
    }
}
