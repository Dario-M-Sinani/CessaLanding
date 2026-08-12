<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalCategoriaResource\Pages;
use App\Models\PersonalCategoria;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PersonalCategoriaResource extends Resource
{
    protected static ?string $model = PersonalCategoria::class;

    protected static ?string $cluster = \App\Filament\Clusters\Personal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?string $modelLabel = 'Categoría de Personal';

    protected static ?string $pluralModelLabel = 'Categorías de Personal';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->helperText('Se muestra en el menú "Personal" del sitio y como título de la página.')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set) {
                        if ($operation === 'create') {
                            $set('alias', Str::slug($state));
                        }
                    }),
                Forms\Components\TextInput::make('alias')
                    ->label('Alias (URL: /personal/alias)')
                    ->helperText('Se completa solo a partir del Nombre. Una vez creada la categoría queda fijo -- cambiarlo rompería el link ya publicado.')
                    ->required()
                    ->maxLength(60)
                    ->unique(ignoreRecord: true)
                    ->rules(['alpha_dash'])
                    // Solo visible para SYSTEM -- ADMIN ni lo ve (se sigue autocompletando solo
                    // desde el Nombre en segundo plano, vía el afterStateUpdated de arriba, así
                    // que el valor se arma igual aunque el campo esté oculto para ese rol).
                    ->hidden(fn () => ! auth()->user()?->hasRole(User::ROLE_SYSTEM))
                    // Y aun para SYSTEM, editable solo al crear (mismo criterio que
                    // ContentResource::category_id, ver ESTADO_SEGURIDAD_MIGRACION.md §3.34):
                    // disabled() deja de dehydratar el campo (CanBeDisabled.php), así que al
                    // editar el valor guardado no se toca aunque se vea en pantalla.
                    ->disabled(fn (?PersonalCategoria $record) => $record !== null),
                // "Orden de Visualización" se sacó del formulario a pedido del usuario -- una
                // categoría nueva siempre cae al final sola (ver
                // CreatePersonalCategoria::mutateFormDataBeforeCreate()), sin que nadie tenga
                // que pensar en el número. Reordenar categorías existentes queda pendiente
                // como una mejora de drag-and-drop más adelante (ver §3.31).
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('personal'))
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('alias')->label('Alias')->copyable(),
                Tables\Columns\TextColumn::make('personal_count')->label('Personal Asignado'),
                Tables\Columns\TextColumn::make('position')->label('Orden')->sortable(),
            ])
            ->defaultSort('position')
            ->actions([
                Tables\Actions\EditAction::make(),
                // No se usa DeleteAction::make() directo -- una categoría con personal
                // asignado no se puede borrar (los dejaría sin categoría, invisibles en el
                // sitio público), se avisa en vez de fallar en silencio con la FK nullOnDelete.
                Tables\Actions\Action::make('eliminar')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (PersonalCategoria $record): void {
                        if ($record->personal()->exists()) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('Esta categoría tiene personal asignado. Reasigná o eliminá esas personas primero.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()->title('Categoría eliminada')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonalCategorias::route('/'),
            'create' => Pages\CreatePersonalCategoria::route('/create'),
            'edit' => Pages\EditPersonalCategoria::route('/{record}/edit'),
        ];
    }
}
