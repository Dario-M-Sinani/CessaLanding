<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipResource\Pages;
use App\Models\Tip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TipResource extends Resource
{
    protected static ?string $model = Tip::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationLabel = 'Consejos Importantes';

    protected static ?string $modelLabel = 'Consejo';

    protected static ?string $pluralModelLabel = 'Consejos Importantes';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tip_type')
                    ->label('Tipo')
                    ->options([
                        'TEXT' => 'Texto',
                        'VIDEO' => 'Video',
                    ])
                    ->default('TEXT')
                    ->required()
                    ->live(),
                Forms\Components\Textarea::make('text')
                    ->label(fn (Forms\Get $get) => $get('tip_type') === 'VIDEO' ? 'URL del Video' : 'Texto del Consejo')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('position')
                    ->label('Orden de Visualización')
                    ->numeric()
                    ->default(0),
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
                Tables\Columns\TextColumn::make('text')->label('Consejo')->limit(60),
                Tables\Columns\TextColumn::make('tip_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === 'VIDEO' ? 'Video' : 'Texto')
                    ->badge(),
                Tables\Columns\TextColumn::make('position')->label('Orden'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
            ])
            ->filters([])
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
            'index' => Pages\ListTips::route('/'),
            'create' => Pages\CreateTip::route('/create'),
            'edit' => Pages\EditTip::route('/{record}/edit'),
        ];
    }
}
