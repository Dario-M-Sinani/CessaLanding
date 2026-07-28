<?php

namespace App\Filament\Resources\CollectionsPointResource\Pages;

use App\Filament\Resources\CollectionsPointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollectionsPoints extends ListRecords
{
    protected static string $resource = CollectionsPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
