<?php

namespace App\Filament\Resources\ReciboResource\Pages;

use App\Filament\Resources\ReciboResource;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRecibo extends ViewRecord
{
    protected static string $resource = ReciboResource::class;

    public function getTitle(): string
    {
        return 'Cobro QR '.$this->record->alias;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('actualizarEstado')
                ->label('Actualizar Estado')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('sincronizarEstado'),

            Actions\Action::make('inhabilitar')
                ->label('Inhabilitar')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('El QR dejará de aceptar pagos. Esta acción no se puede deshacer.')
                ->visible(fn (): bool => $this->record->status === PaymentStatus::Pendiente)
                ->action(function (): void {
                    try {
                        app(QrPaymentProviderInterface::class)->disable($this->record->alias);
                    } catch (QrPaymentException $e) {
                        Notification::make()
                            ->title('No se pudo inhabilitar el cobro')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->update(['status' => PaymentStatus::Inhabilitado]);

                    Notification::make()
                        ->title('Cobro inhabilitado')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function sincronizarEstado(): void
    {
        try {
            $result = app(QrPaymentProviderInterface::class)->status($this->record->alias);
        } catch (QrPaymentException $e) {
            Notification::make()
                ->title('No se pudo consultar el estado')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->record->update([
            'status' => $result->status,
            'paid_at' => $result->processedAt ?? $this->record->paid_at,
            'provider_order_number' => $result->providerOrderNumber ?? $this->record->provider_order_number,
            'payer_account' => $result->payerAccount ?? $this->record->payer_account,
            'payer_name' => $result->payerName ?? $this->record->payer_name,
            'payer_document' => $result->payerDocument ?? $this->record->payer_document,
        ]);

        Notification::make()
            ->title('Estado actualizado')
            ->body('Estado actual: '.$result->status->label())
            ->success()
            ->send();
    }
}
