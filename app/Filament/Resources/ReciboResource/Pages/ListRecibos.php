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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRecibos extends ListRecords
{
    protected static string $resource = ReciboResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarCsv')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->exportarCsv()),

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
                        // No hay meses/N° de cliente en este flujo (lo genera el staff a mano,
                        // no viene de Consulta de Deuda) -- descripción simple sin ese detalle,
                        // ver PagoQrController::generar() para el caso con más datos.
                        'descripcion_pago' => sprintf(
                            '%s %s — %s — Vence %s',
                            number_format((float) $data['amount'], 2),
                            $data['currency'],
                            $data['glosa'],
                            Carbon::parse($data['expires_at'])->format('d/m/Y'),
                        ),
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

    // Mismo patrón que ClientContactUpdateResource::exportarCsv() -- CSV con BOM UTF-8 para
    // que Excel en Windows lo abra directo sin romper tildes/ñ, streameado en chunks (no carga
    // todos los recibos en memoria de una vez).
    private function exportarCsv(): StreamedResponse
    {
        $filename = 'cobros-qr-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            // Orden pedido explícitamente por el usuario: Monto, Glosa, Descripción y N° Cliente
            // primero (lo que identifica el pago de un vistazo), Banco Destino después de esos
            // 4 -- el resto de columnas (auditoría/trazabilidad) va al final, sin quitarlas.
            fputcsv($handle, [
                'Monto', 'Moneda', 'Glosa', 'Descripción de Pago', 'N° Cliente', 'Banco Destino',
                'Alias', 'Fecha de Creación', 'Estado', 'Cuenta Destino',
                'N° de Orden', 'Pagador', 'Documento Pagador', 'Fecha de Pago', 'Generado Por',
            ]);

            Recibo::query()
                ->with('creator')
                ->orderByDesc('created_at')
                ->chunk(200, function ($recibos) use ($handle) {
                    foreach ($recibos as $recibo) {
                        fputcsv($handle, [
                            number_format((float) $recibo->amount, 2),
                            $recibo->currency,
                            $recibo->glosa,
                            $recibo->descripcion_pago,
                            $recibo->nro_cliente,
                            $recibo->destination_bank,
                            $recibo->alias,
                            $recibo->created_at?->format('d/m/Y H:i'),
                            $recibo->status->label(),
                            $recibo->destination_account,
                            $recibo->provider_order_number,
                            $recibo->payer_name,
                            $recibo->payer_document,
                            $recibo->paid_at?->format('d/m/Y H:i'),
                            $recibo->creator?->name,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
