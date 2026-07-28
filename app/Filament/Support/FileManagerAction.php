<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Actions\Action;

class FileManagerAction
{
    /**
     * A "suffixAction" for a URL TextInput that opens the visual file
     * browser (folders + thumbnails, same as the Gestor de Archivos page)
     * so the user can pick an already-uploaded file or upload a new one.
     * Selecting a file writes its public URL into $targetField.
     */
    public static function make(string $targetField, string $directory = ''): Action
    {
        return Action::make('elegirArchivo_' . $targetField)
            ->label('Elegir del Gestor de Archivos')
            ->icon('heroicon-o-folder-open')
            ->color('gray')
            ->modalHeading('Elegir o Subir Archivo')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->modalWidth('2xl')
            ->modalContent(fn () => view('filament.forms.file-picker-modal', [
                'target' => $targetField,
                'directory' => $directory,
            ]));
    }
}
