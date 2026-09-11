<?php

namespace App\Filament\Resources\ReciboResource\Pages;

use App\Filament\Resources\ReciboResource;
use App\Models\Recibo;
use App\Services\CessaApiService;
use App\Services\Payments\Contracts\QrPaymentProviderInterface;
use App\Services\Payments\DataTransferObjects\QrPaymentRequest;
use App\Services\Payments\Exceptions\QrPaymentException;
use App\Services\Payments\PaymentStatus;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRecibos extends ListRecords
{
    protected static string $resource = ReciboResource::class;

    // Mismo tope que PagoQrController::LIMITE_MONTO_QR (duplicado a propósito, no
    // refactorizado a un solo lugar -- mismo criterio que ya usa ese controller
    // consigo mismo, para no acoplar el flujo público al panel de administración).
    private const LIMITE_MONTO_QR = 50000.0;

    private const MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarCsv')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->exportarCsv()),

            Actions\Action::make('generarCobroQr')
                ->label('Generar Cobro QR')
                ->icon('heroicon-o-qr-code')
                ->modalHeading('Generar Cobro QR desde N° de Cliente')
                ->modalDescription('Se consulta la deuda real en SIIC y el monto/glosa se arman solos -- no se escriben a mano.')
                ->form([
                    Forms\Components\TextInput::make('nro_cliente')
                        ->label('N° de Cliente')
                        ->numeric()
                        ->required()
                        ->rule('digits_between:1,10'),
                    Forms\Components\TextInput::make('cantidad_meses')
                        ->label('Cantidad de Meses a Cobrar')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Se cobran siempre los más antiguos primero. Vacío = todo lo pendiente.'),
                ])
                ->action(fn (array $data) => $this->generarCobroQrDesdeCliente($data))
                ->modalSubmitActionLabel('Generar QR'),
        ];
    }

    // Misma lógica de negocio que PagoQrController::generar() (deuda real de SIIC, límite de
    // Bs 50.000, un solo QR pendiente por cliente, glosa/descripción armadas del mismo modo) --
    // duplicada a propósito, no refactorizada a un solo lugar compartido, porque ese controller
    // ya está auditado y este flujo tiene una diferencia real: acá no hace falta el segundo
    // factor (N° de Cuenta) porque quien genera el cobro ya es personal interno autenticado,
    // no un desconocido consultando por internet.
    private function generarCobroQrDesdeCliente(array $data): void
    {
        $ahoraBolivia = Carbon::now('America/La_Paz');
        if ($ahoraBolivia->hour === 23 && $ahoraBolivia->minute === 59) {
            Notification::make()
                ->title('No disponible por el corte diario')
                ->body('El sistema comercial corta entre las 23:59 y las 00:00. Intenta nuevamente en unos minutos.')
                ->warning()
                ->send();

            return;
        }

        $nroCliente = (string) $data['nro_cliente'];

        try {
            $siic = app(CessaApiService::class)->consultaDeuda([
                'nro_cliente' => $nroCliente,
                'ver_deuda' => 'si',
            ]);
        } catch (\Exception $e) {
            Notification::make()->title('No se pudo conectar con SIIC')->body('Intenta más tarde.')->danger()->send();

            return;
        }

        if (isset($siic['error']) || empty($siic['nro_cliente'])) {
            Notification::make()->title('No se encontró ningún abonado con ese N° de Cliente')->danger()->send();

            return;
        }

        $pendientes = collect($siic['deuda'] ?? [])
            ->sortBy([
                fn ($a, $b) => ((int) $a['anio']) <=> ((int) $b['anio']),
                fn ($a, $b) => ((int) $a['mes']) <=> ((int) $b['mes']),
            ])
            ->values()
            ->map(function (array $item) {
                $magnitud = abs((float) ($item['importe'] ?? 0));
                $item['importe_firmado'] = ($item['debito_credito'] ?? 'DEBITO') === 'CREDITO' ? -$magnitud : $magnitud;

                return $item;
            });

        if ($pendientes->isEmpty()) {
            Notification::make()->title('Esta cuenta no registra deuda pendiente')->warning()->send();

            return;
        }

        // filled() en vez de ?? -- un TextInput numérico vacío en Filament dehidrata como
        // string vacío, no null, así que "??" sola no detecta "el campo quedó en blanco" y
        // terminaría cobrando 0 meses en vez de "todo lo pendiente" (comportamiento por defecto).
        $cantidadMeses = min(
            filled($data['cantidad_meses'] ?? null) ? (int) $data['cantidad_meses'] : $pendientes->count(),
            $pendientes->count()
        );
        $aPagar = $pendientes->take($cantidadMeses);
        $monto = round((float) $aPagar->sum(fn ($item) => (float) $item['importe_firmado']), 2);

        if ($monto <= 0) {
            Notification::make()
                ->title('La selección no tiene un monto positivo para cobrar')
                ->body('Probá con más meses (puede haber una conciliación/crédito entre los seleccionados).')
                ->danger()
                ->send();

            return;
        }

        if ($monto > self::LIMITE_MONTO_QR) {
            Notification::make()
                ->title('Monto supera el límite permitido por QR')
                ->body('Bs. '.number_format($monto, 2).' supera el tope de Bs. '.number_format(self::LIMITE_MONTO_QR, 0, '.', '.').'.')
                ->danger()
                ->send();

            return;
        }

        $montoTexto = number_format($monto, 2, '.', '');
        $glosaConPrefijo = "CESSA {$cantidadMeses} comp {$nroCliente} B{$montoTexto}";
        $glosaSinPrefijo = "{$cantidadMeses} comp {$nroCliente} B{$montoTexto}";
        $glosa = mb_strlen($glosaConPrefijo) <= 30 ? $glosaConPrefijo : $glosaSinPrefijo;

        $mesesTexto = $cantidadMeses === 1 ? '1 mes' : "{$cantidadMeses} meses";
        $comprobantesTexto = $cantidadMeses === 1 ? '1 comprobante' : "{$cantidadMeses} comprobantes";
        $periodosExactos = $aPagar->map(fn (array $item) => self::MESES[(int) $item['mes']].' '.$item['anio'])->all();
        $fechasExactas = count($periodosExactos) > 1
            ? implode(', ', array_slice($periodosExactos, 0, -1)).' y '.end($periodosExactos)
            : $periodosExactos[0];
        $montoLegible = floor($monto) == $monto ? number_format($monto, 0, '.', '') : number_format($monto, 2, '.', '');
        $descripcionPago = "Pago de {$mesesTexto} ({$comprobantesTexto}) por {$montoLegible} bolivianos"
            ." — Cliente {$nroCliente} — Períodos: {$fechasExactas} — Generado desde el panel";

        $provider = app(QrPaymentProviderInterface::class);
        $alias = 'CESSA-ADMIN-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        $expiresAt = now()->addMinutes(5);

        // Mismo criterio que el flujo público: nunca dos QR "Pendiente" a la vez para el mismo
        // cliente, sin importar si el anterior lo generó el sitio o el panel.
        DB::transaction(function () use ($nroCliente, $provider) {
            $anteriores = Recibo::where('nro_cliente', $nroCliente)
                ->where('status', PaymentStatus::Pendiente)
                ->lockForUpdate()
                ->get();

            foreach ($anteriores as $anterior) {
                try {
                    $provider->disable($anterior->alias);
                } catch (QrPaymentException $e) {
                    report($e);
                }

                $anterior->update(['status' => PaymentStatus::Inhabilitado]);
            }
        });

        try {
            $result = $provider->generate(new QrPaymentRequest(
                alias: $alias,
                amount: $monto,
                currency: 'BOB',
                description: $glosa,
                expiresAt: $expiresAt,
                callbackUrl: route('pagos.sip.callback'),
                singleUse: true,
            ));
        } catch (QrPaymentException $e) {
            report($e);
            Notification::make()->title('No se pudo generar el QR de pago')->body($e->getMessage())->danger()->send();

            return;
        }

        $qrImagePath = "recibos/qr/{$alias}.png";
        Storage::disk('public')->put($qrImagePath, base64_decode($result->qrImageBase64));

        Recibo::create([
            'provider' => $provider->key(),
            'alias' => $alias,
            'nro_cliente' => $nroCliente,
            'amount' => $monto,
            'currency' => 'BOB',
            'glosa' => $glosa,
            'descripcion_pago' => $descripcionPago,
            'debt_items' => $aPagar->values()->all(),
            'status' => PaymentStatus::Pendiente,
            'expires_at' => $expiresAt,
            'qr_image_path' => $qrImagePath,
            'provider_qr_id' => $result->providerQrId,
            'provider_transaction_id' => $result->providerTransactionId,
            'destination_bank' => $result->destinationBank,
            'destination_account' => $result->destinationAccount,
            'created_by_user_id' => auth()->id(),
        ]);

        Notification::make()
            ->title('Cobro QR generado correctamente')
            ->body("Cliente {$nroCliente} — Bs. {$montoTexto} — Alias: {$alias}")
            ->success()
            ->send();
    }

    // Mismo patrón que ClientContactUpdateResource::exportarCsv() -- CSV con BOM UTF-8 para
    // que Excel en Windows lo abra directo sin romper tildes/ñ, streameado en chunks (no carga
    // todos los recibos en memoria de una vez).
    private function exportarCsv(): StreamedResponse
    {
        $filename = 'cobros-qr-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            // Orden pedido explícitamente por el usuario: Monto, Glosa, Descripción y N° Cliente
            // primero (lo que identifica el pago de un vistazo), Banco Destino después de esos
            // 4 -- el resto de columnas (auditoría/trazabilidad) va al final, sin quitarlas.
            fputcsv($handle, [
                'Monto', 'Moneda', 'Glosa', 'Descripción de Pago', 'N° Cliente', 'Banco Destino',
                'Alias', 'Fecha de Creación', 'Estado', 'Cuenta Destino',
                'N° de Orden', 'Pagador', 'Documento Pagador', 'Fecha de Pago', 'Generado Por',
            ]);

            Recibo::query()
                ->with('creator')
                ->orderByDesc('created_at')
                ->chunk(200, function ($recibos) use ($handle) {
                    foreach ($recibos as $recibo) {
                        fputcsv($handle, [
                            number_format((float) $recibo->amount, 2),
                            $recibo->currency,
                            $recibo->glosa,
                            $recibo->descripcion_pago,
                            $recibo->nro_cliente,
                            $recibo->destination_bank,
                            $recibo->alias,
                            $recibo->created_at?->format('d/m/Y H:i'),
                            $recibo->status->label(),
                            $recibo->destination_account,
                            $recibo->provider_order_number,
                            $recibo->payer_name,
                            $recibo->payer_document,
                            $recibo->paid_at?->format('d/m/Y H:i'),
                            $recibo->creator?->name,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
