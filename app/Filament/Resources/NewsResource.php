<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Noticias';

    protected static ?string $modelLabel = 'Noticia';

    protected static ?string $pluralModelLabel = 'Noticias';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título de la Noticia')
                    ->required()
                    ->maxLength(180)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('summary')
                    ->label('Resumen Breve')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('full_text')
                    ->label('Contenido Completo')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image_url')
                    ->label('Imagen del Comunicado')
                    ->helperText('Se usa como cabecera del comunicado pop-up en el inicio. Opcional: si no se sube, el pop-up se muestra solo con texto.')
                    ->directory('noticias')
                    ->image()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('tags')
                    ->label('Etiquetas (ej. CESSA, Redes, Sucre)')
                    ->maxLength(150),
                Forms\Components\Toggle::make('popup')
                    ->label('Mostrar como Comunicado Pop-up en el Inicio')
                    ->helperText('Actívalo para que esta noticia aparezca como aviso emergente al ingresar a la página de inicio. Se muestra la más reciente marcada.'),
                Forms\Components\Select::make('published')
                    ->label('Estado de Publicación')
                    ->options([
                        'S' => 'Publicado',
                        'N' => 'Borrador / Oculto',
                    ])
                    ->default('S'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\ImageColumn::make('image_url')->label('Imagen')->square(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tags')->label('Etiquetas'),
                Tables\Columns\IconColumn::make('popup')
                    ->label('Pop-up'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
