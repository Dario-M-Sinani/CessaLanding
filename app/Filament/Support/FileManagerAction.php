<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
            ->modalWidth('3xl')
            ->modalContent(fn () => view('filament.forms.file-picker-modal', [
                'target' => $targetField,
                'directory' => $directory,
            ]))
            // Setear el campo y cerrar el modal deben pasar en el MISMO request Livewire
            // (callMountedFormComponentAction ya hace ambas cosas atómicamente, es el mismo
            // mecanismo que usa cualquier acción de Filament al enviar su formulario). Antes esto
            // se hacía en dos pasos desde JS ($wire.set() + cerrar el modal a mano) y la respuesta
            // del primer paso podía llegar a mitad de la transición de cierre del segundo,
            // remorfeando el modal a medio cerrar -- se veía "colgado" (ver ESTADO_SEGURIDAD_MIGRACION.md).
            //
            // $set/$get (en vez de armar "data.{$targetField}" a mano con data_set($livewire, ...))
            // resuelven la ruta RELATIVA al contenedor real del campo al que está atada esta acción
            // (Action::resolveDefaultClosureDependencyForEvaluationByName() los liga a
            // $this->getComponent()->getSetCallback()/getGetCallback()) -- así funciona igual si el
            // campo vive en la raíz del formulario o anidado dentro de un item de Repeater (antes
            // "data.{$targetField}" siempre apuntaba a la raíz y fallaba en silencio dentro de un
            // Repeater, por eso no se usaba ahí -- ver ESTADO_SEGURIDAD_MIGRACION.md §-1octies).
            ->action(function (array $arguments, Set $set, Get $get) use ($targetField, $valueType, $multiple) {
                $value = $valueType === 'path' ? ($arguments['path'] ?? null) : ($arguments['url'] ?? null);

                if (blank($value)) {
                    return;
                }

                if ($valueType === 'path') {
                    // FileUpload guarda su estado en vivo como array incluso sin multiple()
                    // -- un string suelto rompe su propia validación interna (BaseFileUpload
                    // registra una regla que exige array $value). $multiple decide si se
                    // agrega a lo que ya había o se reemplaza, nunca si se envuelve o no.
                    $current = $multiple ? array_values((array) ($get($targetField) ?? [])) : [];
                    $set($targetField, [...$current, $value]);
                } else {
                    $set($targetField, $value);
                }
            });
    }
}
