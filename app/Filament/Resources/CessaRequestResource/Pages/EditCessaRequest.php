<?php

namespace App\Filament\Resources\CessaRequestResource\Pages;

use App\Filament\Resources\CessaRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCessaRequest extends EditRecord
{
    protected static string $resource = CessaRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
