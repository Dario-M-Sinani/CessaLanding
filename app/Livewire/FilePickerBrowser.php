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
                'url' => $this->disk()->url($file),
                'ext' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
            ])
            ->sortBy('name')
            ->values();
    }

    public function updatedNuevoArchivo(): void
    {
        $this->validate([
            'nuevoArchivo' => 'file|max:102400',
        ]);

        $this->nuevoArchivo->storeAs($this->path, $this->nuevoArchivo->getClientOriginalName(), 'public');

        $this->nuevoArchivo = null;
    }

    public function render()
    {
        return view('livewire.file-picker-browser');
    }
}
