<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReciboResource\Pages;
use App\Models\Recibo;
use App\Services\Payments\PaymentStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ReciboResource extends Resource
{
    protected static ?string $model = Recibo::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Cobros QR';

    protected static ?string $modelLabel = 'Cobro QR';

    protected static ?string $pluralModelLabel = 'Cobros QR';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 15;

    // Solo lectura desde el listado estándar de Filament -- los Recibos se crean con la acción
    // "Generar Cobro QR" (llama al proveedor real) y se editan solo a través de las acciones de
    // la vista de detalle (inhabilitar / actualizar estado), nunca con un formulario libre que
    // podría desincronizar el estado real que tiene SIP.
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Datos del Cobro')
                ->columns(3)
                ->schema([
                    TextEntry::make('alias')
                        ->label('Alias')
                        ->copyable(),
                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                        ->color(fn (PaymentStatus $state): string => match ($state) {
                            PaymentStatus::Pendiente => 'warning',
                            PaymentStatus::Pagado, PaymentStatus::Facturado => 'success',
                            PaymentStatus::Inhabilitado => 'secondary',
                            PaymentStatus::Expirado, PaymentStatus::Error, PaymentStatus::ErrorFacturacion => 'danger',
                        }),
                    TextEntry::make('provider')
                        ->label('Proveedor'),
                    TextEntry::make('amount')
                        ->label('Monto')
                        ->formatStateUsing(fn (Recibo $record): string => number_format((float) $record->amount, 2).' '.$record->currency),
                    TextEntry::make('glosa')
                        ->label('Glosa (enviada al proveedor)'),
                    TextEntry::make('descripcion_pago')
                        ->label('Descripción de Pago (constancia interna)')
                        ->placeholder('—')
                        ->columnSpanFull(),
                    TextEntry::make('nro_cliente')
                        ->label('N° Cliente')
                        ->placeholder('— (generado desde el panel)'),
                    TextEntry::make('expires_at')
                        ->label('Vence')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('destination_bank')
                        ->label('Banco Destino')
                        ->placeholder('—'),
                    TextEntry::make('destination_account')
                        ->label('Cuenta Destino')
                        ->placeholder('—'),
                    TextEntry::make('creator.name')
                        ->label('Generado por')
                        ->placeholder('—'),
                ]),

            Section::make('Código QR')
                ->schema([
                    ImageEntry::make('qr_image_path')
                        ->label('')
                        ->disk('public')
                        ->height(260)
                        ->visibility('public'),
                ])
                ->visible(fn (Recibo $record): bool => filled($record->qr_image_path)),

            Section::make('Datos del Pago')
                ->columns(2)
                ->schema([
                    TextEntry::make('paid_at')
                        ->label('Fecha de Pago')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('provider_order_number')
                        ->label('N° de Orden')
                        ->placeholder('—'),
                    TextEntry::make('payer_name')
                        ->label('Nombre del Pagador')
                        ->placeholder('—'),
                    TextEntry::make('payer_document')
                        ->label('Documento del Pagador')
                        ->placeholder('—'),
                    TextEntry::make('payer_account')
                        ->label('Cuenta del Pagador')
                        ->placeholder('—'),
                ])
                ->visible(fn (Recibo $record): bool => in_array($record->status, [
                    PaymentStatus::Pagado, PaymentStatus::Facturado, PaymentStatus::ErrorFacturacion,
                ], true)),

            Section::make('Facturación (api-cobranzas-bancos)')
                ->columns(2)
                ->schema([
                    TextEntry::make('cobranzas_uuid')
                        ->label('UUID de Transacción')
                        ->placeholder('—')
                        ->copyable(),
                    TextEntry::make('facturado_at')
                        ->label('Facturado el')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('comprobante_path')
                        ->label('Comprobante')
                        ->placeholder('—')
                        ->formatStateUsing(fn (): string => 'Descargar factura (PDF)')
                        ->url(fn (Recibo $record): ?string => $record->comprobante_path
                            ? Storage::disk('public')->url($record->comprobante_path)
                            : null)
                        ->openUrlInNewTab()
                        ->color('primary')
                        ->visible(fn (Recibo $record): bool => filled($record->comprobante_path)),
                    TextEntry::make('facturacion_error')
                        ->label('Motivo del error')
                        ->placeholder('—')
                        ->color('danger')
                        ->columnSpanFull()
                        ->visible(fn (Recibo $record): bool => $record->status === PaymentStatus::ErrorFacturacion),
                ])
                ->visible(fn (Recibo $record): bool => in_array($record->status, [
                    PaymentStatus::Pagado, PaymentStatus::Facturado, PaymentStatus::ErrorFacturacion,
                ], true)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alias')
                    ->label('Alias')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn (Recibo $record): string => number_format((float) $record->amount, 2).' '.$record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('glosa')
                    ->label('Glosa')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::Pendiente => 'warning',
                        PaymentStatus::Pagado, PaymentStatus::Facturado => 'success',
                        PaymentStatus::Inhabilitado => 'secondary',
                        PaymentStatus::Expirado, PaymentStatus::Error, PaymentStatus::ErrorFacturacion => 'danger',
                    }),
                Tables\Columns\TextColumn::make('destination_bank')
                    ->label('Banco Destino')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payer_name')
                    ->label('Pagador')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Vence')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Generado por')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('descripcion_pago')
                    ->label('Descripción de Pago')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(array_combine(
                        array_map(fn (PaymentStatus $s) => $s->value, PaymentStatus::cases()),
                        array_map(fn (PaymentStatus $s) => $s->label(), PaymentStatus::cases()),
                    )),
                Tables\Filters\SelectFilter::make('currency')
                    ->label('Moneda')
                    ->options(['BOB' => 'Bolivianos', 'USD' => 'Dólares']),
            ])
            ->actions([
                Tables\Actions\Action::make('ticket')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Recibo $record): string => route('comprobante.ticket', ['alias' => $record->alias]))
                    ->openUrlInNewTab()
                    ->visible(fn (Recibo $record): bool => in_array($record->status, [PaymentStatus::Pagado, PaymentStatus::Facturado], true)),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecibos::route('/'),
            'view' => Pages\ViewRecibo::route('/{record}'),
        ];
    }
}
