<?php

namespace App\Filament\Resources\ClientContactUpdateResource\Pages;

use App\Filament\Resources\ClientContactUpdateResource;
use App\Models\ClientContactUpdate;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListClientContactUpdates extends ListRecords
{
    protected static string $resource = ClientContactUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->exportarCsv()),
        ];
    }

    private function exportarCsv(): StreamedResponse
    {
        $filename = 'actualizacion-datos-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // BOM para que Excel en Windows detecte UTF-8 y no rompa tildes/ñ.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['N° Cliente', 'N° Cuenta', 'Nombre', 'Correo', 'Celular', 'Actualizado']);

            ClientContactUpdate::query()
                ->orderByDesc('created_at')
                ->chunk(200, function ($registros) use ($handle) {
                    foreach ($registros as $registro) {
                        fputcsv($handle, [
                            $registro->nro_cliente,
                            $registro->cuenta,
                            $registro->client_name,
                            $registro->email,
                            $registro->phone,
                            $registro->created_at?->format('d/m/Y H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
