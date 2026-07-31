<?php

namespace App\Filament\Resources\CessaRequestResource\Pages;

use App\Filament\Resources\CessaRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCessaRequest extends ViewRecord
{
    protected static string $resource = CessaRequestResource::class;

    public function getTitle(): string
    {
        return 'Solicitud '.($this->record->formatted_code ?? '');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('marcarAtendido')
                ->label('Marcar como Atendido')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== 'APROBADO')
                ->action(fn () => $this->setStatus('APROBADO')),

            Actions\Action::make('rechazar')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== 'RECHAZADO')
                ->action(fn () => $this->setStatus('RECHAZADO')),

            Actions\EditAction::make(),
        ];
    }

    protected function setStatus(string $status): void
    {
        $this->record->update([
            'status' => $status,
            'modified_by' => auth()->user()?->name,
        ]);

        $this->refreshFormData(['status', 'modified_by']);

        Notification::make()
            ->title('Estado actualizado')
            ->success()
            ->send();
    }
}
