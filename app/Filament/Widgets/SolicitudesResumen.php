<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CessaRequestResource;
use App\Models\CessaRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SolicitudesResumen extends BaseWidget
{
    // Solo tiene sentido para Atención al Cliente -- es el único rol con acceso a Solicitudes.
    public static function canView(): bool
    {
        return auth()->user()?->role === User::ROLE_CUSTOMER_SERVICE;
    }

    protected function getStats(): array
    {
        $pendientesPorTipo = fn (array $tipos) => CessaRequest::whereIn('service_type', $tipos)
            ->where('status', 'PENDIENTE')
            ->count();

        return [
            Stat::make('Nuevas Conexiones', $pendientesPorTipo(['NUEVO_SUMINISTRO']))
                ->description('Pendientes de atención')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success')
                ->url(CessaRequestResource::getUrl('index')),

            Stat::make('Solicitudes de Suspensión', $pendientesPorTipo(['SUSPENSION_TEMPORAL', 'SUSPENSION_DEFINITIVA']))
                ->description('Pendientes de atención')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('danger')
                ->url(CessaRequestResource::getUrl('index')),

            Stat::make('Otras Solicitudes', $pendientesPorTipo(['OTRAS_SOLICITUDES']))
                ->description('Pendientes de atención')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->url(CessaRequestResource::getUrl('index')),
        ];
    }
}
