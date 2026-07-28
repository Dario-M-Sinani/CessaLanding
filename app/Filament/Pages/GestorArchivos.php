<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GestorArchivos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Gestor de Archivos';

    protected static ?string $title = 'Gestor de Archivos';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.pages.gestor-archivos';

    public string $path = '';

    public ?string $confirmDeletePath = null;

    public string $confirmDeleteType = 'file';

    public string $confirmDeleteLabel = '';

    protected function disk()
    {
        return Storage::disk('public');
    }

    protected function normalizePath(string $path): string
    {
        $path = str_replace(['..', '\\'], '', $path);

        return trim($path, '/');
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
                'size' => $this->disk()->size($file),
                'ext' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
            ])
            ->sortBy('name')
            ->values();
    }

    public function askDeleteFile(string $path, string $label): void
    {
        $this->confirmDeletePath = $path;
        $this->confirmDeleteType = 'file';
        $this->confirmDeleteLabel = $label;
        $this->dispatch('open-modal', id: 'confirmar-eliminar');
    }

    public function askDeleteFolder(string $path, string $label): void
    {
        $this->confirmDeletePath = $path;
        $this->confirmDeleteType = 'folder';
        $this->confirmDeleteLabel = $label;
        $this->dispatch('open-modal', id: 'confirmar-eliminar');
    }

    public function confirmDelete(): void
    {
        if ($this->confirmDeletePath === null) {
            return;
        }

        if ($this->confirmDeleteType === 'folder') {
            $this->disk()->deleteDirectory($this->confirmDeletePath);
            Notification::make()->title('Carpeta eliminada')->success()->send();
        } else {
            $this->disk()->delete($this->confirmDeletePath);
            Notification::make()->title('Archivo eliminado')->success()->send();
        }

        $this->confirmDeletePath = null;
        $this->dispatch('close-modal', id: 'confirmar-eliminar');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crearCarpeta')
                ->label('Nueva Carpeta')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->form([
                    TextInput::make('nombre')
                        ->label('Nombre de la Carpeta')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $slug = Str::slug($data['nombre'], '_');
                    $this->disk()->makeDirectory(trim($this->path . '/' . $slug, '/'));

                    Notification::make()->title('Carpeta creada')->success()->send();
                }),
            Action::make('subir')
                ->label('Subir Archivo')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('archivo')
                        ->label('Archivo')
                        ->required()
                        ->maxSize(100 * 1024)
                        ->disk('public')
                        ->directory(fn () => $this->path !== '' ? $this->path : '.')
                        ->visibility('public')
                        ->preserveFilenames(),
                ])
                ->action(function (): void {
                    Notification::make()->title('Archivo subido')->success()->send();
                }),
        ];
    }
}
