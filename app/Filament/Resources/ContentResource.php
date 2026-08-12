<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\Category;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
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
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('clients')
                                    ->label('Clientes')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('employees')
                                    ->label('Total Trabajadores')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('permanentes')
                                    ->label('Trabajadores Fijos (opcional)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('eventuales')
                                    ->label('Trabajadores Eventuales (opcional)')
                                    ->numeric(),
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
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('male')
                                    ->label('Personal Masculino')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('female')
                                    ->label('Personal Femenino')
                                    ->numeric()
                                    ->required(),
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
