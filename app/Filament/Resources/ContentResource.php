<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\Category;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                    ->options(function (?Content $record) {
                        $query = Category::query();

                        // "Personal" quedó obsoleta como categoría de Contenidos -- reemplazada
                        // por el módulo Personal real (ver ESTADO_SEGURIDAD_MIGRACION.md §3.26/
                        // §3.31). Se saca de las opciones para que nadie la elija en contenido
                        // nuevo. Si un registro viejo ya la tiene asignada (ej. el Content de
                        // prueba de §3.31), se deja igual para que su nombre siga resolviendo
                        // bien acá abajo, aunque el campo esté deshabilitado.
                        if (! $record || $record->category?->title !== 'Personal') {
                            $query->where('title', '!=', 'Personal');
                        }

                        return $query->pluck('title', 'id');
                    })
                    // Editable solo al crear -- una vez que un Content ya tiene categoría
                    // asignada, cambiarla a mano podría romper los menús dinámicos que dependen
                    // de ella (Consumidor/La Compañía, ver §3.20ter/§3.30) sin que quede claro
                    // en el panel por qué. Se sigue mostrando (deshabilitado, no oculto) para
                    // que quede claro en qué categoría está cada página.
                    ->disabled(fn (?Content $record) => $record !== null)
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(240)
                    ->live(onBlur: true)
                    // Autocompleta el alias a partir del título, pero solo al crear -- si se
                    // hiciera también al editar, cambiar el título de una página ya publicada
                    // le movería la URL sola y rompería los links que ya apunten a ella.
                    // Sigue siendo editable a mano en cualquier momento (ej. para acortarlo).
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set) {
                        if ($operation === 'create') {
                            $set('alias', Str::slug($state));
                        }
                    })
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('alias')
                    ->label('Alias (URL: /contenido/alias)')
                    ->required()
                    ->maxLength(240)
                    ->unique(ignoreRecord: true)
                    ->rules(['alpha_dash'])
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('summary')
                    ->label('Resumen Breve')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('full_text')
                    ->label('Contenido Completo')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Section::make('Documentos Adjuntos')
                    ->description('Formularios, resoluciones, PDFs, etc. Se muestran como tarjetas debajo del contenido -- no hace falta editar el HTML de arriba para agregar/quitar uno.')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('documentos')
                            ->label('')
                            ->schema([
                                Forms\Components\FileUpload::make('archivo')
                                    ->label('Archivo')
                                    ->required()
                                    ->directory(fn (Get $get) => 'documentos/'.Str::slug($get('../../alias') ?: 'contenidos'))
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/msword',
                                        'application/zip', 'application/x-zip-compressed',
                                    ])
                                    ->helperText('PDF, Excel, Word o ZIP. Arrastrá el archivo o hacé clic para buscarlo.')
                                    ->preserveFilenames()
                                    // Autocompleta el Título con el nombre del archivo apenas se sube -- para
                                    // que quien carga el documento no tenga que pensar un título desde cero,
                                    // solo ajustarlo si hace falta. No pisa un título que ya se haya escrito
                                    // a mano (ej. al reemplazar el archivo de un documento existente).
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        // $state acá es el TemporaryUploadedFile de Livewire (todavía no se
                                        // guardó en disco) -- necesita ->getClientOriginalName() para el
                                        // nombre real que eligió el usuario; el $state "a secas" (como string
                                        // o el nombre temporal en disco) es algo tipo "phpXXXXXX", no sirve.
                                        if (blank($state) || filled($get('titulo')) || ! is_object($state) || ! method_exists($state, 'getClientOriginalName')) {
                                            return;
                                        }

                                        $nombre = pathinfo($state->getClientOriginalName(), PATHINFO_FILENAME);
                                        $set('titulo', Str::headline(str_replace(['-', '_'], ' ', $nombre)));
                                    })
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('titulo')
                                    ->label('Título')
                                    ->required()
                                    ->helperText('Se completa solo con el nombre del archivo -- lo podés cambiar.')
                                    ->columnSpan(2),
                            ])
                            ->columns(3)
                            ->itemLabel(fn (array $state): ?string => $state['titulo'] ?? 'Documento nuevo')
                            ->reorderableWithButtons()
                            ->addActionLabel('Agregar Documento')
                            ->collapsible()
                            ->collapsed(),
                    ]),
                Forms\Components\Section::make('Estructura Organizacional (bloque superior de la página)')
                    ->description('Organigrama y documento PEI que se muestran arriba del Directorio/Plantel Ejecutivo. Solo aplica a esta página.')
                    ->visible(fn (?Content $record) => $record?->alias === 'estructura-organizacional')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Toggle::make('show_org_chart')
                            ->label('Mostrar Sección de Estructura Organizacional')
                            ->helperText('Si está apagado, el organigrama y el documento PEI no se muestran en la página, aunque estén cargados.')
                            ->default(true)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('org_chart_image')
                            ->label('Imagen del Organigrama')
                            ->directory('contenido')
                            ->image()
                            ->hintAction(FileManagerAction::make('org_chart_image', 'contenido', 'path'))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('pei_document')
                            ->label('Documento PEI (PDF)')
                            ->helperText('Plan Estratégico Institucional, descargable desde la página.')
                            ->directory('contenido')
                            ->acceptedFileTypes(['application/pdf'])
                            ->hintAction(FileManagerAction::make('pei_document', 'contenido', 'path'))
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Estadísticas de Personal (gráficos de la página)')
                    ->description('Un renglón por año. Los gráficos y el texto de resumen de la página se arman solos a partir de estos datos — no hace falta editar imágenes ni el texto para actualizarlos cada año.')
                    ->visible(fn (?Content $record) => $record?->alias === 'recursos-humanos')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('staff_yearly_stats')
                            ->label('Clientes y Personal (por año)')
                            ->helperText('El último año cargado alimenta la frase "En la gestión X se cuenta con Y trabajadores". "Fijos" y "Eventuales" son opcionales: si se cargan ambos para un año 2020 o posterior, ese año aparece en el gráfico de Clientes y Personal (Clientes en barras; Total, Fijos y Eventuales como líneas).')
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->label('Año')
                                    ->integer()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                                Forms\Components\TextInput::make('clients')
                                    ->label('Clientes')
                                    ->integer()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                                Forms\Components\TextInput::make('employees')
                                    ->label('Total Trabajadores')
                                    ->integer()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                                Forms\Components\TextInput::make('permanentes')
                                    ->label('Trabajadores Fijos (opcional)')
                                    ->integer()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                                Forms\Components\TextInput::make('eventuales')
                                    ->label('Trabajadores Eventuales (opcional)')
                                    ->integer()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->addActionLabel('Agregar Año')
                            ->defaultItems(0),
                        Forms\Components\Repeater::make('gender_yearly_stats')
                            ->label('Personal Masculino / Femenino (por año)')
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->label('Año')
                                    ->integer()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                                Forms\Components\TextInput::make('male')
                                    ->label('Personal Masculino')
                                    ->integer()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                                Forms\Components\TextInput::make('female')
                                    ->label('Personal Femenino')
                                    ->integer()
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->addActionLabel('Agregar Año')
                            ->defaultItems(0),
                    ]),
                Forms\Components\FileUpload::make('image_url')
                    ->label(fn (?Content $record) => $record?->alias === 'estructura-organizacional'
                        ? 'Foto del Plantel Ejecutivo / Directorio Actual'
                        : 'Imagen (opcional)')
                    ->helperText('Se muestra arriba del contenido de esta página. Se puede ocultar sin borrarla con el interruptor "Mostrar imagen".')
                    ->directory('contenido')
                    ->image()
                    ->hintAction(FileManagerAction::make('image_url', 'contenido', 'path'))
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('show_image')
                    ->label(fn (?Content $record) => $record?->alias === 'estructura-organizacional'
                        ? 'Mostrar Foto del Plantel'
                        : 'Mostrar Imagen')
                    ->helperText('Si está apagado, la imagen no se muestra en la página aunque esté cargada.')
                    ->default(true),
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
            ->modifyQueryUsing(fn ($query) => $query->with('category'))
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagen')
                    ->square()
                    // ver FileManagerAction::resolveUrl() -- ImageColumn no reconoce como
                    // URL válida el formato en que este campo puede tener guardada la ruta.
                    ->getStateUsing(fn ($record) => FileManagerAction::resolveUrl($record->image_url)),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('alias')->label('Alias')->searchable()->copyable(),
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
