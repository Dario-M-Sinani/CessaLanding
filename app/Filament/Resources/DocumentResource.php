<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\Document;
use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-arrow-down';

    protected static ?string $navigationLabel = 'Documentos Institucionales';

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Documentos';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 25;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título del Documento')
                    ->required()
                    ->maxLength(240)
                    ->columnSpanFull(),
                Forms\Components\Select::make('publication_id')
                    ->label('Categoría / Proceso relacionado')
                    ->relationship('publication', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Elige a qué proceso pertenece (Licitación, Invitación, Convocatoria, Remate de Activos u Otros). Si no existe todavía, créalo aquí mismo.')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->label('Título del proceso')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options(Publication::getTypes())
                            ->default('OTHERS')
                            ->required(),
                        Forms\Components\Select::make('published')
                            ->label('Estado')
                            ->options(['S' => 'Publicado', 'N' => 'Oculto'])
                            ->default('S'),
                    ])
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('url')
                    ->label('Archivo PDF / Documento')
                    ->helperText('PDF, imagen (JPG/PNG) o ZIP, máximo 100 MB.')
                    ->directory('institucional')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/zip', 'application/x-zip-compressed'])
                    ->maxSize(100 * 1024)
                    ->downloadable()
                    ->openable()
                    ->required()
                    ->hintAction(FileManagerAction::make('url', 'institucional', 'path'))
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
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('publication.type')
                    ->label('Categoría')
                    ->formatStateUsing(fn (?string $state): string => $state ? (Publication::getTypes()[$state] ?? $state) : 'Sin categoría')
                    ->badge(),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
                Tables\Columns\TextColumn::make('position')->label('Orden'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y'),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
