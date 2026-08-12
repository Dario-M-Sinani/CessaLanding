<?php

namespace App\Filament\Resources\PersonalCategoriaResource\Pages;

use App\Filament\Resources\PersonalCategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonalCategorias extends ListRecords
{
    protected static string $resource = PersonalCategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
