<?php

namespace App\Filament\Resources\AetnCommunicationResource\Pages;

use App\Filament\Resources\AetnCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAetnCommunication extends EditRecord
{
    protected static string $resource = AetnCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
