<?php

namespace App\Filament\Resources\ReciboResource\Pages;

use App\Filament\Resources\ReciboResource;
use App\Models\Recibo;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListRecibos extends ListRecords
{
    protected static string $resource = ReciboResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generarCobroQr')
                ->label('Generar Cobro QR')
                ->icon('heroicon-o-qr-code')
                ->form([
                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->required()
                        ->minValue(0.01),
                    Forms\Components\Select::make('currency')
                        ->label('Moneda')
                        ->options([
                            'BOB' => 'Bolivianos (BOB)',
                            'USD' => 'Dólares (USD)',
                        ])
                        ->default('BOB')
                        ->required(),
                    Forms\Components\TextInput::make('glosa')
                        ->label('Glosa / Detalle')
                        ->required()
                        ->maxLength(30)
                        ->helperText('Máximo 30 caracteres (lo exige el proveedor de pagos).'),
                    Forms\Components\DatePicker::make('expires_at')
                        ->label('Fecha de Vencimiento')
                        ->required()
                        ->native(false)
                        ->minDate(now())
                        ->default(now()->addDays(3)),
                ])
                ->action(function (array $data): void {
                    $provider = app(QrPaymentProviderInterface::class);
                    $alias = 'CESSA-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));

                    try {
                        $result = $provider->generate(new QrPaymentRequest(
                            alias: $alias,
                            amount: (float) $data['amount'],
                            currency: $data['currency'],
                            description: $data['glosa'],
                            expiresAt: Carbon::parse($data['expires_at'])->endOfDay(),
                            callbackUrl: route('pagos.sip.callback'),
                            singleUse: true,
                        ));
                    } catch (QrPaymentException $e) {
                        Notification::make()
                            ->title('No se pudo generar el cobro QR')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $qrImagePath = "recibos/qr/{$alias}.png";
                    Storage::disk('public')->put($qrImagePath, base64_decode($result->qrImageBase64));

                    Recibo::create([
                        'provider' => $provider->key(),
                        'alias' => $alias,
                        'amount' => $data['amount'],
                        'currency' => $data['currency'],
                        'glosa' => $data['glosa'],
                        'status' => PaymentStatus::Pendiente,
                        'expires_at' => $result->expiresAt,
                        'qr_image_path' => $qrImagePath,
                        'provider_qr_id' => $result->providerQrId,
                        'provider_transaction_id' => $result->providerTransactionId,
                        'destination_bank' => $result->destinationBank,
                        'destination_account' => $result->destinationAccount,
                        'created_by_user_id' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Cobro QR generado correctamente')
                        ->body("Alias: {$alias}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
