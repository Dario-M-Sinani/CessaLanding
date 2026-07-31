<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduledOutageResource\Pages;
use App\Models\ScheduledOutage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduledOutageResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

    protected static ?string $model = ScheduledOutage::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt-slash';

    protected static ?string $navigationLabel = 'Cortes Programados';

    protected static ?string $modelLabel = 'Corte Programado';

    protected static ?string $pluralModelLabel = 'Cortes Programados';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('reason')
                    ->label('Motivo del Mantenimiento / Interrupción')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('location')
                    ->label('Zonas y Barrios Afectados')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('execution_date')
                    ->label('Fecha del Corte')
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Hora de Inicio')
                    ->required(),
                Forms\Components\TimePicker::make('finish_time')
                    ->label('Hora de Finalización')
                    ->required(),
                Forms\Components\Select::make('published')
                    ->label('Estado')
                    ->options([
                        'S' => 'Publicado',
                        'N' => 'Oculto',
                    ])
                    ->default('S'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('execution_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('execution_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')->label('Inicio')->time('H:i'),
                Tables\Columns\TextColumn::make('finish_time')->label('Fin')->time('H:i'),
                Tables\Columns\TextColumn::make('location')
                    ->label('Zonas Afectadas')
                    ->limit(50),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
            ])
            ->filters([
                Tables\Filters\Filter::make('proximos')
                    ->label('Solo próximos')
                    ->query(fn ($query) => $query->where('execution_date', '>=', now()->toDateString()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListScheduledOutages::route('/'),
            'create' => Pages\CreateScheduledOutage::route('/create'),
            'edit' => Pages\EditScheduledOutage::route('/{record}/edit'),
        ];
    }
}
