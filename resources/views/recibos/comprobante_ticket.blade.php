<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante - {{ $factura['periodo'] ?? 'CESSA' }}</title>
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0;
            padding: 0;
        }

        body { 
            font-family: 'Courier New', Courier, Consolas, monospace, sans-serif;
            background-color: #e5e7eb; 
            color: #000000;
            padding: 20px 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Barra de acciones en pantalla */
        .action-bar {
            max-width: 320px;
            margin: 0 auto 15px auto;
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            font-family: sans-serif;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #000;
            text-align: center;
        }

        .btn-print {
            background-color: #000000;
            color: #ffffff;
            flex: 1;
        }

        .btn-close {
            background-color: #ffffff;
            color: #000000;
        }

        /* Contenedor del Ticket Térmico (80mm / 58mm) */
        .ticket { 
            width: 100%;
            max-width: 320px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 16px 12px; 
            color: #000000;
            border: 1px solid #000000;
            font-size: 12px;
            line-height: 1.35;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .line-dashed {
            border-top: 1px dashed #000000;
            margin: 10px 0;
        }

        .line-solid {
            border-top: 2px solid #000000;
            margin: 10px 0;
        }

        .line-double {
            border-top: 3px double #000000;
            margin: 10px 0;
        }

        /* Encabezado */
        .header h2 {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header p {
            font-size: 11px;
        }

        /* Bloque de Total */
        .total-container {
            border: 2px solid #000000;
            padding: 8px 6px;
            margin: 10px 0;
            text-align: center;
        }
        .total-container .total-label {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .total-container .total-amount {
            font-size: 22px;
            font-weight: 900;
            margin: 2px 0;
        }
        .total-container .total-literal {
            font-size: 9px;
            margin-top: 2px;
            line-height: 1.2;
        }

        /* Filas de datos */
        .data-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        .data-row .label {
            color: #000000;
        }
        .data-row .val {
            font-weight: bold;
            text-align: right;
            max-width: 65%;
            word-break: break-word;
        }

        /* Tabla de Conceptos */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 11px;
        }
        .items-table th {
            border-bottom: 1px dashed #000000;
            padding-bottom: 4px;
            font-weight: bold;
            font-size: 10px;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        /* Sección QR */
        .qr-box {
            text-align: center;
            margin: 10px 0;
        }
        .qr-box img {
            width: 140px;
            height: 140px;
            display: inline-block;
        }
        .qr-box p {
            font-size: 9px;
            margin-top: 4px;
        }

        /* Pie de página */
        .footer {
            font-size: 9px;
            line-height: 1.3;
            text-align: center;
            margin-top: 8px;
        }
        .footer p {
            margin: 2px 0;
            word-break: break-all;
        }

        /* Reglas estrictas para impresión térmica */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                background: #ffffff;
                color: #000000;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .ticket {
                border: none;
                max-width: 100%;
                width: 100%;
                padding: 8px 4px;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<!-- Botones de Acción (Solo en pantalla) -->
<div class="action-bar no-print">
    <button onclick="window.print()" class="btn btn-print">
        IMPRIMIR
    </button>
    <button onclick="window.close()" class="btn btn-close">
        CERRAR
    </button>
</div>

<!-- Ticket Térmico en Blanco y Negro -->
<div class="ticket">
    <!-- Encabezado -->
    <div class="header text-center">
        <h2>CESSA</h2>
        <p class="bold">COMPAÑÍA ELÉCTRICA SUCRE S.A.</p>
        <p>NIT: 1000831029</p>
        <p class="bold" style="margin-top: 4px;">COMPROBANTE DE SERVICIO</p>
        <p>Periodo: <strong>{{ $factura['periodo'] ?? 'N/A' }}</strong></p>
    </div>

    <div class="line-dashed"></div>

    <!-- Total a Pagar / Pagado -->
    <div class="total-container">
        <div class="total-label">TOTAL PAGADO</div>
        <div class="total-amount">Bs. {{ number_format((float) ($factura['total_pagar'] ?? 0), 2) }}</div>
        @if(!empty($factura['total_pagar_literal']))
            <div class="total-literal bold uppercase">{{ $factura['total_pagar_literal'] }}</div>
        @endif
    </div>

    <!-- Datos del Cliente y Suministro -->
    <div class="data-row">
        <span class="label">CLIENTE:</span>
        <span class="val">{{ $factura['cliente_nombre'] ?? 'N/A' }}</span>
    </div>
    <div class="data-row">
        <span class="label">N° CUENTA:</span>
        <span class="val">{{ $factura['cliente_cuenta'] ?? 'N/A' }}</span>
    </div>
    <div class="data-row">
        <span class="label">N° CLIENTE:</span>
        <span class="val">{{ $factura['cliente_codigo'] ?? $factura['nro_cliente'] ?? 'N/A' }}</span>
    </div>
    <div class="data-row">
        <span class="label">MEDIDOR:</span>
        <span class="val">{{ $factura['nro_medidor'] ?? 'N/A' }}</span>
    </div>
    @if(isset($factura['consumo_kwh']) && $factura['consumo_kwh'] !== null)
    <div class="data-row">
        <span class="label">CONSUMO:</span>
        <span class="val">{{ number_format((float) $factura['consumo_kwh'], 2) }} kWh</span>
    </div>
    @endif
    @if(!empty($factura['fecha_cancelacion']))
    <div class="data-row">
        <span class="label">FECHA PAGO:</span>
        <span class="val">{{ $factura['fecha_cancelacion'] }}</span>
    </div>
    @endif

    <div class="line-dashed"></div>

    <!-- Detalle de Conceptos -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">CONCEPTO</th>
                <th class="text-right">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factura['detalle'] ?? [] as $item)
            <tr>
                <td class="text-left">{{ $item['descripcion'] ?? $item['detalle'] ?? 'Concepto' }}</td>
                <td class="text-right bold">
                    {{ number_format((float) ($item['subtotal'] ?? $item['importe'] ?? 0), 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td class="text-left">Servicio Eléctrico</td>
                <td class="text-right bold">
                    {{ number_format((float) ($factura['total_pagar'] ?? 0), 2) }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="line-solid"></div>

    <!-- Código QR Impuestos / SIAT -->
    @if(!empty($qrImage))
    <div class="qr-box">
        <img src="{{ $qrImage }}" alt="QR SIAT">
        <p>ESCANEA PARA VERIFICAR EN SIAT</p>
    </div>
    @endif

    <div class="line-dashed"></div>

    <!-- Datos Fiscales y Pie de Impresión -->
    <div class="footer">
        @if(!empty($factura['nro_factura']))
            <p><strong>FACTURA N°:</strong> {{ $factura['nro_factura'] }}</p>
        @endif
        @if(!empty($factura['codigo_autorizacion']))
            <p><strong>CUF:</strong> {{ $factura['codigo_autorizacion'] }}</p>
        @endif
        @if(!empty($factura['cajero']))
            <p><strong>CAJERO:</strong> {{ $factura['cajero'] }} | <strong>CAJA:</strong> {{ $factura['caja'] ?? 'WEB' }}</p>
        @endif
        <p style="margin-top: 6px;">*** CESSA VIRTUAL ***</p>
        <p>GRACIAS POR SU PAGO</p>
    </div>
</div>

</body>
</html>
