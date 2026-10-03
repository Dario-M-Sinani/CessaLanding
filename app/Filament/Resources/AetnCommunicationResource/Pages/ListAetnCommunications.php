<?php

namespace App\Filament\Resources\AetnCommunicationResource\Pages;

use App\Filament\Resources\AetnCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAetnCommunications extends ListRecords
{
    protected static string $resource = AetnCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
