<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Models\Category;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Contenidos';

    protected static ?string $modelLabel = 'Contenido';

    protected static ?string $pluralModelLabel = 'Contenidos';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->options(Category::pluck('title', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(240)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('alias')
                    ->label('Alias (URL: /contenido/alias)')
                    ->required()
                    ->maxLength(240)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('summary')
                    ->label('Resumen Breve')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('full_text')
                    ->label('Contenido Completo')
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
                        'N' => 'Borrador / Oculto',
                    ])
                    ->default('S'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('alias')->label('Alias'),
                Tables\Columns\TextColumn::make('category.title')->label('Categoría'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
            ])
            ->filters([])
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
