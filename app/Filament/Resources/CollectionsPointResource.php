<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionsPointResource\Pages;
use App\Models\Bank;
use App\Models\CollectionsPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CollectionsPointResource extends Resource
{
    protected static ?string $model = CollectionsPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Puntos de Cobranza';

    protected static ?string $modelLabel = 'Punto de Cobranza';

    protected static ?string $pluralModelLabel = 'Puntos de Cobranza';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(60)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('phones')
                    ->label('Teléfonos')
                    ->maxLength(60),
                Forms\Components\TextInput::make('attention_schedule')
                    ->label('Horario de Atención')
                    ->maxLength(60),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitud')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitud')
                    ->numeric(),
                Forms\Components\Select::make('bank_id')
                    ->label('Banco')
                    ->options(fn () => Bank::pluck('name', 'id'))
                    ->searchable()
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
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('address')->label('Dirección')->limit(30),
                Tables\Columns\TextColumn::make('phones')->label('Teléfonos'),
                Tables\Columns\TextColumn::make('attention_schedule')->label('Horario de Atención')->limit(30),
                Tables\Columns\TextColumn::make('bank.name')->label('Banco')->sortable(),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bank_id')
                    ->label('Banco')
                    ->options(fn () => Bank::pluck('name', 'id')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionsPoints::route('/'),
            'create' => Pages\CreateCollectionsPoint::route('/create'),
            'edit' => Pages\EditCollectionsPoint::route('/{record}/edit'),
        ];
    }
}
