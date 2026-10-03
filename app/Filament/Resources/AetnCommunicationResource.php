<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AetnCommunicationResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\AetnCommunication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AetnCommunicationResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

    protected static ?string $model = AetnCommunication::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Comunicados AETN';

    protected static ?string $modelLabel = 'Comunicado AETN';

    protected static ?string $pluralModelLabel = 'Comunicados AETN';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->placeholder('COMUNICADO 07/2026')
                    ->required()
                    ->maxLength(180)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->helperText('Opcional. Breve resumen del comunicado.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('image_url')
                    ->label('Imagen del Comunicado')
                    ->required()
                    ->maxLength(350)
                    ->live(onBlur: true)
                    ->suffixAction(FileManagerAction::make('image_url', 'comunicados-aetn'))
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('preview')
                    ->label('Vista Previa Actual')
                    ->content(fn ($get) => filled($get('image_url'))
                        ? new HtmlString(
                            '<img src="' . e(FileManagerAction::resolveUrl($get('image_url'))) . '" style="max-height:240px;border-radius:0.75rem;border:1px solid #3f3f46;" />'
                        )
                        : 'Sin imagen todavía.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('document_url')
                    ->label('Documento PDF (opcional)')
                    ->helperText('Solo si además del afiche/imagen hay un PDF oficial para descargar.')
                    ->maxLength(350)
                    ->suffixAction(FileManagerAction::make('document_url', 'comunicados-aetn'))
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('published_date')
                    ->label('Fecha del Comunicado')
                    ->helperText('Determina el orden: el más reciente aparece primero.')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(now())
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
            ->defaultSort('published_date', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Vista Previa')
                    ->square()
                    ->size(80)
                    ->getStateUsing(fn ($record) => FileManagerAction::resolveUrl($record->image_url)),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('published_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
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
            'index' => Pages\ListAetnCommunications::route('/'),
            'create' => Pages\CreateAetnCommunication::route('/create'),
            'edit' => Pages\EditAetnCommunication::route('/{record}/edit'),
        ];
    }
}
