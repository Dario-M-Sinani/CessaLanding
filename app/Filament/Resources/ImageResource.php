<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\Image;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Imagenes';

    protected static ?string $modelLabel = 'Imagen';

    protected static ?string $pluralModelLabel = 'Imágenes';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(180)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('url')
                    ->label('URL de la Imagen')
                    ->url()
                    ->required()
                    ->maxLength(350)
                    ->suffixAction(FileManagerAction::make('url', 'galeria'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->label('Galería')
                    ->options([
                        'galeria' => 'Galería de Fotos (general)',
                        'trabajadores' => 'Galería de Trabajadores',
                    ])
                    ->default('galeria')
                    ->required(),
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
                Tables\Columns\ImageColumn::make('url')->label('Vista Previa')->square(),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Galería')
                    ->formatStateUsing(fn ($state) => $state === 'trabajadores' ? 'Trabajadores' : 'Fotos (general)')
                    ->badge(),
                Tables\Columns\TextColumn::make('position')->label('Orden'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Galería')
                    ->options([
                        'galeria' => 'Galería de Fotos (general)',
                        'trabajadores' => 'Galería de Trabajadores',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }
}
