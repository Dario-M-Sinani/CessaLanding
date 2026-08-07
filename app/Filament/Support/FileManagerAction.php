<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\Storage;

class FileManagerAction
{
    /**
     * Convierte cualquiera de los dos formatos que este picker puede haber guardado
     * (ver make() más abajo) en una URL absoluta usable en un <img src>:
     * - 'url': hotlink https://... o ruta "/storage/..." ya relativa al dominio.
     * - 'path': ruta relativa al disco sin "/storage/" (lo que guarda FileUpload nativo).
     */
    public static function resolveUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return url($value);
        }

        return Storage::disk('public')->url($value);
    }

    /**
     * An action (suffixAction for TextInput, hintAction for FileUpload) that opens
     * the visual file browser (folders + thumbnails, same as the Gestor de Archivos
     * page) so the user can pick an already-uploaded file or upload a new one.
     *
     * @param  string  $valueType  'url' writes the absolute public URL (for plain URL TextInputs,
     *                             e.g. BankResource::img_url). 'path' writes the disk-relative path
     *                             (for FileUpload fields, e.g. ContentResource::image_url), matching
     *                             what FileUpload itself stores when a file is uploaded normally.
     * @param  bool  $multiple  when true, appends the picked file to the field's existing array
     *                          instead of replacing it (for FileUpload::multiple() fields).
     */
    public static function make(string $targetField, string $directory = '', string $valueType = 'url', bool $multiple = false): Action
    {
        return Action::make('elegirArchivo_' . str_replace('.', '_', $targetField))
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
                'valueType' => $valueType,
                'multiple' => $multiple,
            ]));
    }
}
