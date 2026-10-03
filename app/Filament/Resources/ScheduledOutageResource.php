<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduledOutageResource\Pages;
use App\Models\ScheduledOutage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduledOutageResource extends Resource
{
    use \App\Filament\Resources\Concerns\RestrictedFromCustomerService;

    protected static ?string $model = ScheduledOutage::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt-slash';

    protected static ?string $navigationLabel = 'Cortes Programados';

    protected static ?string $modelLabel = 'Corte Programado';

    protected static ?string $pluralModelLabel = 'Cortes Programados';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('type')
                    ->label('Tipo de Corte')
                    ->options([
                        ScheduledOutage::TYPE_PROGRAMADO => 'Programado',
                        ScheduledOutage::TYPE_EMERGENCIA => 'Emergencia',
                    ])
                    ->colors([
                        ScheduledOutage::TYPE_PROGRAMADO => 'info',
                        ScheduledOutage::TYPE_EMERGENCIA => 'danger',
                    ])
                    ->icons([
                        ScheduledOutage::TYPE_PROGRAMADO => 'heroicon-o-calendar-days',
                        ScheduledOutage::TYPE_EMERGENCIA => 'heroicon-o-exclamation-triangle',
                    ])
                    ->helperText('Emergencia: corte imprevisto (choque de poste, falla, clima). Se muestra en rojo en el inicio de la web para que la población sepa que CESSA ya tiene conocimiento, y desaparece sola '.ScheduledOutage::EMERGENCY_VISIBLE_HOURS.' horas después de creada.')
                    ->default(ScheduledOutage::TYPE_PROGRAMADO)
                    ->inline()
                    ->live()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('reason')
                    ->label(fn (Get $get) => self::esEmergencia($get) ? 'Causa de la Emergencia' : 'Motivo del Mantenimiento / Interrupción')
                    ->datalist(fn (Get $get) => self::esEmergencia($get) ? [
                        'CHOQUE DE VEHÍCULO CONTRA POSTE EN ',
                        'CAÍDA DE POSTE EN ',
                        'CAÍDA DE ÁRBOL SOBRE LA RED EN ',
                        'FALLA EN TRANSFORMADOR EN ',
                        'FALLA EN LÍNEA DE MEDIA TENSIÓN EN ',
                        'DESCARGA ATMOSFÉRICA / TORMENTA EN ',
                        'CONDUCTOR CORTADO EN ',
                        'INTERRUPCIÓN DEL SUMINISTRO DESDE EL SISTEMA INTERCONECTADO',
                    ] : [
                        'CAMBIO DE POSTES DE MEDIA TENSIÓN EN ',
                        'MANTENIMIENTO PREVENTIVO EN LA RED DE BAJA TENSIÓN ',
                        'MANTENIMIENTO PREVENTIVO EN LA RED DE MEDIA TENSIÓN ',
                        'MANTENIMIENTO PREVENTIVO CON REEMPLAZO DE POSTES Y ESTRUCTURAS EN ',
                        'MANTENIMIENTO PREVENTIVO DEL PUESTO DE TRANSFORMACIÓN EN ',
                        'REEMPLAZO DE POSTES Y ESTRUCTURAS DE MEDIA TENSIÓN EN ',
                        'RETIRO DE POSTES Y MEJORAS EN LÍNEA DE BAJA TENSIÓN EN ',
                        'CONVERSIÓN DE MONOFÁSICO A TRIFÁSICO DE LÍNEA DE BAJA TENSIÓN EN ',
                        'MODIFICACIÓN DE LÍNEA DE MEDIA TENSIÓN EN ',
                        'AMPLIACIÓN Y MODIFICACIÓN DE LÍNEAS DE BAJA TENSIÓN EN ',
                    ])
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('location')
                    ->label('Zonas y Barrios Afectados')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('affected_institutions')
                    ->label('Instituciones Afectadas')
                    ->helperText('Opcional. Colegios, hospitales, oficinas públicas, etc. que están o estarán sin energía.')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('execution_date')
                    ->label('Fecha del Corte')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Hora de Inicio')
                    ->native(false)
                    ->seconds(false)
                    ->required(),
                Forms\Components\TimePicker::make('finish_time')
                    ->label(fn (Get $get) => self::esEmergencia($get) ? 'Hora Estimada de Reposición' : 'Hora de Finalización')
                    ->helperText(fn (Get $get) => self::esEmergencia($get) ? 'Opcional. Déjela vacía si aún no se conoce.' : null)
                    ->native(false)
                    ->seconds(false)
                    ->required(fn (Get $get) => ! self::esEmergencia($get)),
                Forms\Components\DateTimePicker::make('restored_at')
                    ->label('Servicio Restablecido el')
                    ->helperText('Déjelo vacío mientras la emergencia siga en atención. Una vez restablecido, el aviso se muestra en verde '.ScheduledOutage::RESTORED_VISIBLE_HOURS.' horas más en el inicio y luego desaparece.')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->visible(fn (Get $get) => self::esEmergencia($get)),
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
            ->defaultSort('execution_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === ScheduledOutage::TYPE_EMERGENCIA ? 'Emergencia' : 'Programado')
                    ->color(fn (string $state) => $state === ScheduledOutage::TYPE_EMERGENCIA ? 'danger' : 'info'),
                Tables\Columns\TextColumn::make('execution_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')->label('Inicio')->time('H:i'),
                Tables\Columns\TextColumn::make('finish_time')->label('Fin')->time('H:i'),
                Tables\Columns\TextColumn::make('location')
                    ->label('Zonas Afectadas')
                    ->limit(50),
                Tables\Columns\TextColumn::make('affected_institutions')
                    ->label('Instituciones Afectadas')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40),
                Tables\Columns\TextColumn::make('restored_at')
                    ->label('Restablecido')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder(fn (ScheduledOutage $record) => $record->type === ScheduledOutage::TYPE_EMERGENCIA ? 'En atención' : '—'),
                Tables\Columns\IconColumn::make('published')
                    ->label('Publicado')
                    ->boolean(fn ($state) => $state === 'S'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        ScheduledOutage::TYPE_PROGRAMADO => 'Programado',
                        ScheduledOutage::TYPE_EMERGENCIA => 'Emergencia',
                    ]),
                Tables\Filters\Filter::make('proximos')
                    ->label('Solo próximos')
                    ->query(fn ($query) => $query->where('execution_date', '>=', now()->toDateString()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('restablecer')
                    ->label('Marcar restablecido')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Se registrará la hora actual como hora de reposición del servicio.')
                    ->visible(fn (ScheduledOutage $record) => $record->type === ScheduledOutage::TYPE_EMERGENCIA && $record->restored_at === null)
                    ->action(fn (ScheduledOutage $record) => $record->update(['restored_at' => now()])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function esEmergencia(Get $get): bool
    {
        return $get('type') === ScheduledOutage::TYPE_EMERGENCIA;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduledOutages::route('/'),
            'create' => Pages\CreateScheduledOutage::route('/create'),
            'edit' => Pages\EditScheduledOutage::route('/{record}/edit'),
        ];
    }
}
