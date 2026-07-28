<?php

namespace App\Filament\Resources\CollectionsPointResource\Pages;

use App\Filament\Resources\CollectionsPointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionsPoint extends EditRecord
{
    protected static string $resource = CollectionsPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
