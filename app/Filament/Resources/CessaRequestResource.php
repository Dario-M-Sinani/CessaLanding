<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CessaRequestResource\Pages;
use App\Http\Controllers\NuevaConexionController;
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

    public static function serviceTypeOptions(): array
    {
        return [
            'NUEVO_SUMINISTRO' => 'Nueva Conexión',
            'SUSPENSION_TEMPORAL' => 'Suspensión Temporal',
            'SUSPENSION_DEFINITIVA' => 'Suspensión Definitiva',
            'SUSPENSION_INSPECCION' => 'Suspensión con Inspección',
            'OTRAS_SOLICITUDES' => 'Otras Solicitudes',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Solicitante')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->label('Tipo de Solicitud')
                            ->options(static::serviceTypeOptions())
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

                Forms\Components\Section::make('Detalles Técnicos de la Conexión')
                    ->description('Datos capturados en el paso 2 del formulario público de Nueva Conexión.')
                    ->visible(fn (?CessaRequest $record): bool => $record?->service_type === 'NUEVO_SUMINISTRO')
                    ->schema([
                        Forms\Components\Select::make('area')
                            ->label('Área')
                            ->options(['URBAN' => 'Urbana', 'RURAL' => 'Rural'])
                            ->native(false),
                        Forms\Components\Select::make('consumer_type')
                            ->label('Categoría')
                            ->options(['RESIDENTIAL' => 'Domiciliaria', 'GENERAL' => 'General / Comercial', 'INDUSTRIAL' => 'Industrial'])
                            ->native(false),
                        Forms\Components\Select::make('phase_type')
                            ->label('Tipo de Servicio')
                            ->options(['MONOPHASE' => 'Monofásico', 'TRIPHASIC' => 'Trifásico'])
                            ->native(false),
                    ])->columns(3),

                Forms\Components\Section::make('Detalles de la Suspensión')
                    ->description('Datos capturados en el paso 2 del formulario público de Suspensión de Servicio.')
                    ->visible(fn (?CessaRequest $record): bool => str_starts_with((string) $record?->service_type, 'SUSPENSION'))
                    ->schema([
                        Forms\Components\TextInput::make('last_meter_reading')
                            ->label('Valor Leído en el Medidor')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Detalles de Otras Solicitudes')
                    ->description('Datos capturados en el paso 1 y 2 del formulario público de Otras Solicitudes.')
                    ->visible(fn (?CessaRequest $record): bool => $record?->service_type === 'OTRAS_SOLICITUDES')
                    ->schema([
                        Forms\Components\Select::make('user_type')
                            ->label('Tipo de Cliente')
                            ->options(['POSSESSOR' => 'Poseedor', 'OWNER' => 'Titular'])
                            ->native(false),
                        Forms\Components\Select::make('request_type_id')
                            ->label('Trámite Específico')
                            ->options(NuevaConexionController::OTRAS_REQUEST_TYPE_IDS)
                            ->native(false)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('area')
                            ->label('Área')
                            ->options(['URBAN' => 'Urbana', 'RURAL' => 'Rural'])
                            ->native(false),
                        Forms\Components\Select::make('consumer_type')
                            ->label('Categoría')
                            ->options(['RESIDENTIAL' => 'Domiciliaria', 'GENERAL' => 'General / Comercial', 'INDUSTRIAL' => 'Industrial'])
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Documentos Adjuntos')
                    ->description('Solo lectura -- verificalos antes de aprobar o rechazar la solicitud.')
                    ->schema([
                        Forms\Components\FileUpload::make('url_document_front')
                            ->label('C.I. - Anverso')
                            ->disk('public')
                            ->image()
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\FileUpload::make('url_document_back')
                            ->label('C.I. - Reverso')
                            ->disk('public')
                            ->image()
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\FileUpload::make('url_invoice')
                            ->label('Factura')
                            ->disk('public')
                            ->image()
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?CessaRequest $record): bool => str_starts_with((string) $record?->service_type, 'SUSPENSION') || $record?->service_type === 'OTRAS_SOLICITUDES'),
                        Forms\Components\FileUpload::make('url_last_meter_reading')
                            ->label('Foto del Medidor')
                            ->disk('public')
                            ->image()
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?CessaRequest $record): bool => str_starts_with((string) $record?->service_type, 'SUSPENSION')),
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
                Tables\Columns\TextColumn::make('formatted_code')
                    ->label('Código')
                    ->weight('bold')
                    ->searchable(query: function ($query, string $search) {
                        $numeric = ltrim(preg_replace('/\D/', '', $search), '0');

                        return $query->when($numeric !== '', fn ($q) => $q->where('code_number', $numeric));
                    })
                    ->sortable(['code_number']),
                Tables\Columns\TextColumn::make('fullname')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::serviceTypeOptions()[$state] ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => match ($state) {
                        'NUEVO_SUMINISTRO' => 'success',
                        'SUSPENSION_TEMPORAL', 'SUSPENSION_DEFINITIVA', 'SUSPENSION_INSPECCION' => 'danger',
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
                    ->options(static::serviceTypeOptions()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
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
            'view' => Pages\ViewCessaRequest::route('/{record}'),
            'edit' => Pages\EditCessaRequest::route('/{record}/edit'),
        ];
    }
}
