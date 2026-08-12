<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages;
use App\Filament\Support\FileManagerAction;
use App\Models\Personal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;

    protected static ?string $cluster = \App\Filament\Clusters\Personal::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Listado';

    protected static ?string $modelLabel = 'Persona';

    protected static ?string $pluralModelLabel = 'Personal';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('personal_categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre', fn ($query) => $query->orderBy('position'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre Completo')
                    ->required()
                    ->maxLength(150)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ci')
                    ->label('C.I.')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('tipo_sangre')
                    ->label('Tipo de Sangre')
                    ->options([
                        'O+' => 'O+',
                        'O-' => 'O-',
                        'A+' => 'A+',
                        'A-' => 'A-',
                        'B+' => 'B+',
                        'B-' => 'B-',
                        'AB+' => 'AB+',
                        'AB-' => 'AB-',
                    ]),
                Forms\Components\TextInput::make('celular')
                    ->label('Celular')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->helperText('Cargo, empresa o texto breve que se muestra debajo de los datos en la tarjeta pública.')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('foto')
                    ->label('Fotografía')
                    ->directory('personal')
                    ->image()
                    ->hintAction(FileManagerAction::make('foto', 'personal', 'path'))
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
            ->modifyQueryUsing(fn ($query) => $query->with('categoria'))
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    // ver FileManagerAction::resolveUrl() -- ImageColumn no reconoce como
                    // URL válida el formato en que este campo puede tener guardada la ruta.
                    ->getStateUsing(fn ($record) => FileManagerAction::resolveUrl($record->foto)),
                Tables\Columns\TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge(),
                Tables\Columns\TextColumn::make('ci')->label('C.I.'),
                Tables\Columns\TextColumn::make('celular')->label('Celular'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
            ])
            ->defaultSort('position')
            ->filters([
                Tables\Filters\SelectFilter::make('personal_categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonal::route('/'),
            'create' => Pages\CreatePersonal::route('/create'),
            'edit' => Pages\EditPersonal::route('/{record}/edit'),
        ];
    }
}
