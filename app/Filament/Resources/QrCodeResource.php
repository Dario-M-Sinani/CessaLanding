<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrCodeResource\Pages;
use App\Models\QrCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QrCodeResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

    protected static ?string $model = QrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Códigos QR';

    protected static ?string $modelLabel = 'Código QR';

    protected static ?string $pluralModelLabel = 'Códigos QR';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->helperText('Para identificar este QR en la lista, ej. "Flyer feria 2026" o "Formulario de reclamos".')
                    ->required()
                    ->maxLength(180)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('content')
                    ->label('Contenido del QR (URL o texto)')
                    ->helperText('Lo que se escanea: un link (https://...), un número de WhatsApp, texto libre, etc. El QR no vence — mientras este contenido no cambie, el mismo código sigue funcionando siempre.')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('id')
                    ->label('QR')
                    ->getStateUsing(fn (QrCode $record) => route('qr-codes.image', ['qrCode' => $record->id]))
                    ->square()
                    ->size(64),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Contenido')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar PNG')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (QrCode $record) => route('qr-codes.image', ['qrCode' => $record->id, 'download' => 1]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQrCodes::route('/'),
            'create' => Pages\CreateQrCode::route('/create'),
            'edit' => Pages\EditQrCode::route('/{record}/edit'),
        ];
    }
}
