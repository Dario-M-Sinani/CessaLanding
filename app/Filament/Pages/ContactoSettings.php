<?php

namespace App\Filament\Pages;

use App\Models\ContactInfo;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ContactoSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Contacto (Página Pública)';

    protected static ?string $title = 'Contacto (Página Pública)';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.contacto-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->role !== User::ROLE_CUSTOMER_SERVICE;
    }

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $record = ContactInfo::first();

        $this->form->fill($record ? $record->toArray() : [
            'show_map' => true,
            'phones' => [],
            'schedules' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dirección y Mapa')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Dirección de la Oficina')
                            ->maxLength(350)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitud')
                            ->numeric()
                            ->helperText('En Google Maps: clic derecho sobre el punto exacto → copiá el primer número.'),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitud')
                            ->numeric()
                            ->helperText('El segundo número del mismo clic derecho en Google Maps.'),
                        Forms\Components\Toggle::make('show_map')
                            ->label('Mostrar Mapa en la Página de Contacto')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Teléfonos')
                    ->description('Aparecen en la página de Contacto, en el orden en que los ordenes acá.')
                    ->schema([
                        Forms\Components\Repeater::make('phones')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Etiqueta')
                                    ->placeholder('Línea Gratuita, Call Center, WhatsApp, Emergencias...')
                                    ->required(),
                                Forms\Components\TextInput::make('number')
                                    ->label('Número a Mostrar')
                                    ->placeholder('176, 462 14500, 72876668...')
                                    ->required(),
                                Forms\Components\TextInput::make('link')
                                    ->label('Enlace (opcional)')
                                    ->placeholder('tel:462145500 o https://wa.me/59172876668')
                                    ->helperText('Si se deja vacío, el número se muestra como texto simple.'),
                                Forms\Components\Toggle::make('highlight')
                                    ->label('Destacar (ej. Emergencias)'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('Agregar Teléfono')
                            ->defaultItems(0),
                    ]),
                Forms\Components\Section::make('Horario de Atención')
                    ->description('Un bloque por área/departamento. Podés escribir varias líneas en el horario (ej. días de semana y sábados).')
                    ->schema([
                        Forms\Components\Repeater::make('schedules')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Área / Departamento')
                                    ->placeholder('Plataforma de Atención al Cliente, Call Center...')
                                    ->required(),
                                Forms\Components\Textarea::make('schedule')
                                    ->label('Horario')
                                    ->placeholder("Lun - Vie: 08:00 - 16:00")
                                    ->rows(2)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('Agregar Horario')
                            ->defaultItems(0),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar Cambios')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        ContactInfo::query()->firstOrCreate([])->update($data);

        Notification::make()
            ->title('Información de contacto guardada')
            ->success()
            ->send();
    }
}
