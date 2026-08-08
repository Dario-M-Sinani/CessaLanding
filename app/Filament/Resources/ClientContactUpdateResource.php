<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientContactUpdateResource\Pages;
use App\Models\ClientContactUpdate;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Solo lectura: los registros nacen ya verificados desde la página pública "Actualizar
 * Datos" (App\Http\Controllers\ActualizarDatosController). Por ahora es solo recopilación
 * con exportación a CSV -- los datos se migrarán después a otro sistema.
 */
class ClientContactUpdateResource extends Resource
{
    protected static ?string $model = ClientContactUpdate::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Actualización de Datos';

    protected static ?string $modelLabel = 'Actualización de Datos';

    protected static ?string $pluralModelLabel = 'Actualizaciones de Datos';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 16;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nro_cliente')
                    ->label('N° Cliente')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('cuenta')
                    ->label('N° Cuenta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Nombre')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Celular')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientContactUpdates::route('/'),
        ];
    }
}
