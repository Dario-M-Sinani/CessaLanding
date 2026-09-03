<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PublicationResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

    protected static ?string $model = Publication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Publicaciones';

    protected static ?string $modelLabel = 'Publicación';

    protected static ?string $pluralModelLabel = 'Publicaciones';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción / Detalle del Proceso')
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Tipo de Proceso')
                    ->options(Publication::getTypes())
                    ->default('OTHERS')
                    ->required(),
                Forms\Components\DatePicker::make('expired_date')
                    ->label('Fecha de Vencimiento'),
                Forms\Components\Select::make('published')
                    ->label('Estado de Publicación')
                    ->options([
                        'S' => 'Publicado',
                        'N' => 'Borrador / Oculto',
                    ])
                    ->default('S'),
                Forms\Components\Section::make('Documentos Adjuntos')
                    ->description('Pliegos, bases, formularios, etc. Aparecen como botones de descarga debajo del proceso en /procesos.')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('documents')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\FileUpload::make('url')
                                    ->label('Archivo')
                                    ->required()
                                    ->directory('documentos/procesos')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/msword',
                                        'application/zip', 'application/x-zip-compressed',
                                    ])
                                    ->helperText('PDF, Excel, Word o ZIP.')
                                    ->preserveFilenames()
                                    ->hintAction(FileManagerAction::make('url', 'documentos/procesos', 'path'))
                                    // Mismo patrón que ContentResource: autocompleta el Título con el
                                    // nombre del archivo (getClientOriginalName(), $state acá es el
                                    // TemporaryUploadedFile, no un string), sin pisar un título ya escrito.
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (blank($state) || filled($get('title')) || ! is_object($state) || ! method_exists($state, 'getClientOriginalName')) {
                                            return;
                                        }

                                        $nombre = pathinfo($state->getClientOriginalName(), PATHINFO_FILENAME);
                                        $set('title', Str::headline(str_replace(['-', '_'], ' ', $nombre)));
                                    })
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(240)
                                    ->helperText('Se completa solo con el nombre del archivo -- lo podés cambiar.')
                                    ->columnSpan(2),
                            ])
                            ->columns(3)
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Documento nuevo')
                            ->reorderableWithButtons()
                            ->orderColumn('position')
                            ->collapsed()
                            ->collapsible()
                            ->addActionLabel('Agregar Documento'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => Publication::getTypes()[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('expired_date')
                    ->label('Vence')
                    ->date('d/m/Y'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListPublications::route('/'),
            'create' => Pages\CreatePublication::route('/create'),
            'edit' => Pages\EditPublication::route('/{record}/edit'),
        ];
    }
}
