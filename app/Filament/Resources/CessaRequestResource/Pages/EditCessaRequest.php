<?php

namespace App\Filament\Resources\CessaRequestResource\Pages;

use App\Filament\Resources\CessaRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCessaRequest extends EditRecord
{
    protected static string $resource = CessaRequestResource::class;

    public function getTitle(): string
    {
        return 'Editar Solicitud '.($this->record->formatted_code ?? '');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
