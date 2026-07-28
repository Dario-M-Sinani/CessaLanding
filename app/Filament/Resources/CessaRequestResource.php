<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CessaRequestResource\Pages;
use App\Models\CessaRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CessaRequestResource extends Resource
{
    protected static ?string $model = CessaRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Solicitudes de Trámites';

    protected static ?string $modelLabel = 'Solicitud';

    protected static ?string $pluralModelLabel = 'Solicitudes';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 110;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Solicitante')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->label('Tipo de Solicitud')
                            ->options([
                                'NUEVO_SUMINISTRO' => 'Nueva Conexión',
                                'SUSPENSION_TEMPORAL' => 'Suspensión Temporal',
                                'SUSPENSION_DEFINITIVA' => 'Suspensión Definitiva',
                                'OTRAS_SOLICITUDES' => 'Otras Solicitudes',
                            ])
                            ->native(false)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('fullname')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(180),
                        Forms\Components\TextInput::make('document_number')
                            ->label('Número de C.I. / NIT')
                            ->required()
                            ->maxLength(15),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('mobile_phone')
                            ->label('Teléfono Móvil')
                            ->required()
                            ->maxLength(15),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono Fijo')
                            ->maxLength(15),
                    ])->columns(2),

                Forms\Components\Section::make('Ubicación y Dirección')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Dirección')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('zone')
                            ->label('Zona / Barrio')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('reference')
                            ->label('Referencia de Dirección'),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitud')
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitud')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Estado y Observaciones')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado de la Solicitud')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'EN_PROCESO' => 'En Proceso',
                                'APROBADO' => 'Aprobado',
                                'RECHAZADO' => 'Rechazado',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('observation')
                            ->label('Observaciones / Notas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fullname')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'NUEVO_SUMINISTRO' => 'Nueva Conexión',
                        'SUSPENSION_TEMPORAL' => 'Suspensión Temporal',
                        'SUSPENSION_DEFINITIVA' => 'Suspensión Definitiva',
                        'OTRAS_SOLICITUDES' => 'Otras Solicitudes',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'NUEVO_SUMINISTRO' => 'success',
                        'SUSPENSION_TEMPORAL', 'SUSPENSION_DEFINITIVA' => 'danger',
                        'OTRAS_SOLICITUDES' => 'info',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile_phone')
                    ->label('Móvil'),
                Tables\Columns\TextColumn::make('zone')
                    ->label('Zona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'EN_PROCESO' => 'info',
                        'APROBADO' => 'success',
                        'RECHAZADO' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('send_date')
                    ->label('Fecha Envío')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'EN_PROCESO' => 'En Proceso',
                        'APROBADO' => 'Aprobado',
                        'RECHAZADO' => 'Rechazado',
                    ]),
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Tipo de Solicitud')
                    ->options([
                        'NUEVO_SUMINISTRO' => 'Nueva Conexión',
                        'SUSPENSION_TEMPORAL' => 'Suspensión Temporal',
                        'SUSPENSION_DEFINITIVA' => 'Suspensión Definitiva',
                        'OTRAS_SOLICITUDES' => 'Otras Solicitudes',
                    ]),
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
            'index' => Pages\ListCessaRequests::route('/'),
            'create' => Pages\CreateCessaRequest::route('/create'),
            'edit' => Pages\EditCessaRequest::route('/{record}/edit'),
        ];
    }
}
