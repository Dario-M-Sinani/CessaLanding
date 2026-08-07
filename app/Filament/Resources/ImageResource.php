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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ImageResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

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
                    ->live(onBlur: true)
                    ->suffixAction(FileManagerAction::make('url', 'galeria'))
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('preview')
                    ->label('Vista Previa Actual')
                    ->content(fn ($get) => filled($get('url'))
                        ? new HtmlString(
                            '<img src="' . e(FileManagerAction::resolveUrl($get('url'))) . '" style="max-height:240px;border-radius:0.75rem;border:1px solid #3f3f46;" />'
                        )
                        : 'Sin imagen todavía.')
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
                Forms\Components\Toggle::make('home_carousel')
                    ->label('Usar en el carrusel del Home')
                    ->helperText('Aparece rotando de fondo detrás de "Energía eficiente y servicios en línea" en la página de Inicio (escritorio). El orden dentro del carrusel sigue el campo "Orden de Visualización".')
                    ->default(false)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('home_carousel_mobile')
                    ->label('Usar en el carrusel del Home — versión Mobile')
                    ->helperText('Selección aparte para cuando se ve desde el celular (recorte normal, sin efectos). Si no marcás ninguna imagen acá, mobile usa las mismas del carrusel de escritorio.')
                    ->default(false)
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('mobile_hint')
                    ->label('Recomendación para Mobile')
                    ->content(fn ($get) => static::mobileFitHint($get('url')))
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Lee el tamaño real del archivo (si es local) y sugiere si conviene para el
     * carrusel mobile -- ahí se usa recorte normal (object-cover), así que fotos muy
     * panorámicas pierden los costados al forzarlas a una pantalla angosta y alta.
     */
    protected static function mobileFitHint(?string $url): string
    {
        if (blank($url)) {
            return 'Cargá una imagen para ver la recomendación.';
        }

        $path = match (true) {
            str_starts_with($url, '/storage/') => ltrim(str_replace('/storage/', '', $url), '/'),
            str_starts_with($url, 'http') => null,
            default => $url,
        };

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return 'No se puede analizar el tamaño (es una imagen externa/hotlink) -- revisá visualmente cómo se recorta.';
        }

        $size = @getimagesize(Storage::disk('public')->path($path));

        if (! $size) {
            return 'No se pudo leer el tamaño de la imagen.';
        }

        [$width, $height] = $size;
        $ratio = $width / $height;

        if ($ratio <= 1.15) {
            return "Tamaño real: {$width}×{$height}px. Relación cercana a vertical/cuadrada -- buena candidata para el carrusel mobile.";
        }

        if ($ratio <= 1.6) {
            return "Tamaño real: {$width}×{$height}px. Horizontal moderada -- probablemente recorta bien en mobile, conviene revisarla visualmente antes de activarla.";
        }

        return "Tamaño real: {$width}×{$height}px. Muy panorámica -- el recorte en mobile va a perder buena parte de los costados, mejor evitarla para esa sección.";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('Vista Previa')
                    ->square()
                    ->size(80)
                    // ImageColumn no reconoce como URL válida ni "/storage/..." ni rutas de
                    // disco sin ese prefijo -- FileManagerAction::resolveUrl() normaliza
                    // cualquiera de los formatos que este campo puede tener guardado.
                    ->getStateUsing(fn ($record) => FileManagerAction::resolveUrl($record->url)),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Galería')
                    ->formatStateUsing(fn ($state) => $state === 'trabajadores' ? 'Trabajadores' : 'Fotos (general)')
                    ->badge(),
                Tables\Columns\TextColumn::make('position')->label('Orden'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
                Tables\Columns\IconColumn::make('home_carousel')
                    ->label('Carrusel Home')
                    ->boolean(),
                Tables\Columns\IconColumn::make('home_carousel_mobile')
                    ->label('Carrusel Home (Mobile)')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Galería')
                    ->options([
                        'galeria' => 'Galería de Fotos (general)',
                        'trabajadores' => 'Galería de Trabajadores',
                    ]),
                Tables\Filters\TernaryFilter::make('home_carousel')
                    ->label('Carrusel Home'),
                Tables\Filters\TernaryFilter::make('home_carousel_mobile')
                    ->label('Carrusel Home (Mobile)'),
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
