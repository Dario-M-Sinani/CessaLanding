<?php

namespace App\Filament\Pages;

use App\Models\Publication;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ReportePublicaciones extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Reporte de Publicaciones';

    protected static ?string $title = 'Reporte de Publicaciones';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.reporte-publicaciones';

    public static function canAccess(): bool
    {
        // Solo SYSTEM tiene control absoluto de reportes -- ni ADMIN lo ve.
        return auth()->user()?->hasRole(User::ROLE_SYSTEM) ?? false;
    }

    protected function getTableQuery(): Builder
    {
        return Publication::query()->with(['creator', 'editor']);
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label('Título')
                ->searchable()
                ->wrap(),
            Tables\Columns\TextColumn::make('type')
                ->label('Tipo')
                ->formatStateUsing(fn (string $state): string => Publication::getTypes()[$state] ?? $state)
                ->badge(),
            Tables\Columns\TextColumn::make('creator.name')
                ->label('Subido por')
                ->placeholder('— (registro legacy)')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha de subida')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
            Tables\Columns\TextColumn::make('editor.name')
                ->label('Última edición por')
                ->placeholder('—')
                ->sortable(),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Última edición')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('created_by_user_id')
                ->label('Subido por')
                ->options(fn () => User::orderBy('name')->pluck('name', 'id')),
            Filter::make('created_at')
                ->form([
                    DatePicker::make('desde')->label('Desde'),
                    DatePicker::make('hasta')->label('Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['desde'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['hasta'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                }),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
