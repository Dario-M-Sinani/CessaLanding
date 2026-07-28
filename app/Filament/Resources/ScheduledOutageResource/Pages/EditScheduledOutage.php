<?php

namespace App\Filament\Resources\ScheduledOutageResource\Pages;

use App\Filament\Resources\ScheduledOutageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledOutage extends EditRecord
{
    protected static string $resource = ScheduledOutageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
