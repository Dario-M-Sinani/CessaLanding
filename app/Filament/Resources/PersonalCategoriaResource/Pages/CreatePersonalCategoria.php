<?php

namespace App\Filament\Resources\PersonalCategoriaResource\Pages;

use App\Filament\Resources\PersonalCategoriaResource;
use App\Models\PersonalCategoria;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePersonalCategoria extends CreateRecord
{
    protected static string $resource = PersonalCategoriaResource::class;

    // El alias se autocompleta en el navegador (afterStateUpdated del campo Nombre), pero ese
    // campo está oculto para ADMIN (ver ESTADO_SEGURIDAD_MIGRACION.md §3.31bis) -- un campo
    // oculto en Filament no viaja en el guardado en absoluto (no llega vacío, directamente no
    // está la clave), así que sin este respaldo la creación como ADMIN fallaba con "Field
    // 'alias' doesn't have a default value". Server-side, garantizado, sin depender de que el
    // campo se haya dehidratado del lado del navegador.
    //
    // `position` ya no es un campo del formulario (§3.31ter, a pedido del usuario) -- toda
    // categoría nueva cae al final sola, siempre calculado acá, no en un `default()` de un
    // campo que ya no existe.
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['nombre']);
        }

        $data['position'] = (PersonalCategoria::max('position') ?? -1) + 1;

        return $data;
    }
}
