<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Materiales - {{ $instalacion['titulo'] }} - CESSA</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            color: #000000;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print-bar {
            max-width: 680px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            border: 1px solid #000;
            cursor: pointer;
            text-decoration: none;
            background-color: #000;
            color: #fff;
        }

        .btn-secondary {
            background-color: #fff;
            color: #000;
        }

        .select-tipo {
            padding: 7px 10px;
            font-size: 13px;
            font-weight: bold;
            border: 1px solid #000;
            border-radius: 6px;
            background: #fff;
            color: #000;
        }

        /* Hoja de Impresión en Blanco y Negro */
        .sheet {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border: 1px solid #000000;
            color: #000000;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }

        .header p {
            font-size: 11px;
            margin-top: 3px;
        }

        .badge-box {
            border: 1.5px solid #000;
            padding: 8px 12px;
            margin: 12px 0;
            text-align: center;
        }

        .badge-box h3 {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .badge-box p {
            font-size: 11px;
            margin-top: 2px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin: 15px 0 8px 0;
            letter-spacing: 0.5px;
        }

        /* Tabla de Materiales con Casillas de Verificación */
        .materials-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 15px 0;
            font-size: 11px;
        }

        .materials-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
            font-weight: 900;
            font-size: 10px;
        }

        .materials-table td {
            border-bottom: 1px dashed #666;
            padding: 6px 6px;
            vertical-align: middle;
        }

        .materials-table .col-check {
            width: 35px;
            text-align: center;
        }

        .checkbox-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1.5px solid #000;
        }

        .materials-table .col-cant {
            width: 70px;
            font-weight: bold;
        }

        /* Lista de Requisitos */
        .req-list {
            list-style: none;
            padding-left: 0;
            font-size: 11px;
            margin: 6px 0;
        }

        .req-list li {
            margin-bottom: 4px;
            padding-left: 15px;
            position: relative;
        }

        .req-list li::before {
            content: "•";
            position: absolute;
            left: 2px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #000;
            margin-top: 20px;
            padding-top: 10px;
            font-size: 9px;
            text-align: center;
            line-height: 1.3;
        }

        .footer p {
            margin: 2px 0;
        }

        /* Etiqueta chica (4.10" x 1.70") -- oculta en pantalla, es lo único que se imprime */
        .print-label {
            display: none;
        }

        @media print {
            @page {
                size: 4.10in 1.70in;
                margin: 0;
            }
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .sheet {
                display: none !important;
            }
            /* Tabla en vez de div: <thead> se repite solo en cada página impresa cuando el
               contenido no entra en una sola etiqueta de 1.70" de alto -- así el encabezado
               (título del tipo de instalación) aparece en todas las etiquetas de la tirada,
               no solo en la primera. */
            .print-label {
                display: table;
                width: 4.10in;
                border-collapse: collapse;
                padding: 0.08in 0.16in;
                color: #000000;
                font-family: Arial, Helvetica, 'Segoe UI', sans-serif;
            }
            .print-label thead {
                display: table-header-group;
            }
            .print-label .cabecera td {
                padding: 0.08in 0.16in 0.05in 0.16in;
                border-bottom: 1px solid #000;
            }
            .print-label .marca {
                font-size: 7px;
                font-weight: 900;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }
            .print-label .titulo {
                font-size: 11px;
                font-weight: 900;
                line-height: 1.15;
                margin-top: 2px;
            }
            .print-label .meta {
                font-size: 7px;
                color: #333;
                margin-top: 1px;
            }
            .print-label tbody tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .print-label td.item {
                font-size: 8px;
                line-height: 1.3;
                padding: 2px 0.16in;
                border-bottom: 1px dashed #999;
            }
            .print-label td.item .cant {
                font-weight: bold;
                display: inline-block;
                min-width: 0.5in;
            }
        }
    </style>
</head>
<body>

<!-- Etiqueta chica (4.10" x 1.70"): solo esto se imprime, ver @media print. Es una <table>
     para que el <thead> se repita solo en cada página cuando el listado no entra en una sola. -->
<table class="print-label">
    <thead>
        <tr class="cabecera">
            <td>
                <div class="marca">CESSA - Nueva Instalación</div>
                <div class="titulo">{{ $instalacion['titulo'] }}</div>
                <div class="meta">{{ count($instalacion['materiales']) }} materiales requeridos</div>
            </td>
        </tr>
    </thead>
    <tbody>
        @foreach($instalacion['materiales'] as $m)
        <tr>
            <td class="item"><span class="cant">{{ $m['cantidad'] }}</span>{{ $m['item'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Barra superior de acciones (No se imprime) -->
<div class="no-print-bar">
    <div style="display: flex; gap: 8px; align-items: center;">
        <label for="tipoSelect" style="font-size: 12px; font-weight: bold;">Tipo:</label>
        <select id="tipoSelect" class="select-tipo" onchange="location.href='/instalaciones/imprimir/' + this.value">
            <option value="monofasica" {{ $instalacion['id'] === 'monofasica' ? 'selected' : '' }}>Monofásica (220V)</option>
            <option value="trifasica" {{ $instalacion['id'] === 'trifasica' ? 'selected' : '' }}>Trifásica (380V)</option>
            <option value="tablero-centralizador" {{ $instalacion['id'] === 'tablero-centralizador' ? 'selected' : '' }}>Tablero Centralizador (3+)</option>
        </select>
    </div>

    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn">
            IMPRIMIR ETIQUETA (4.10" x 1.70")
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            CERRAR
        </button>
    </div>
</div>

<!-- Documento Imprimible en Blanco y Negro -->
<div class="sheet">
    <div class="header">
        <h1>COMPAÑÍA ELÉCTRICA SUCRE S.A. (CESSA)</h1>
        <h2>GUÍA DE MATERIALES PARA NUEVA INSTALACIÓN</h2>
        <p>Atención al Cliente: Calle Ayacucho N° 264 | Línea Gratuita: 800-10-2345 | Sucre - Bolivia</p>
    </div>

    <div class="badge-box">
        <h3>{{ $instalacion['titulo'] }}</h3>
        <p>{{ $instalacion['subtitulo'] }}</p>
    </div>

    <div class="section-title">1. LISTA DE MATERIALES REQUERIDOS (CHECKLIST)</div>
    <table class="materials-table">
        <thead>
            <tr>
                <th class="col-check">OK</th>
                <th class="col-cant">CANTIDAD</th>
                <th>DESCRIPCIÓN DEL MATERIAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($instalacion['materiales'] as $m)
            <tr>
                <td class="col-check"><span class="checkbox-box"></span></td>
                <td class="col-cant">{{ $m['cantidad'] }}</td>
                <td>{{ $m['item'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">2. ESPECIFICACIONES TÉCNICAS DEL PUESTO DE MEDICIÓN (NORMA NB 777)</div>
    <ul class="req-list">
        @foreach($instalacion['requisitos_tecnicos'] as $rt)
            <li>{{ $rt }}</li>
        @endforeach
    </ul>

    <div class="section-title">3. REQUISITOS DOCUMENTALES PARA EL TRÁMITE</div>
    <ul class="req-list">
        @foreach($instalacion['requisitos_documentales'] as $rd)
            <li>{{ $rd }}</li>
        @endforeach
    </ul>

    <div class="footer">
        <p><strong>IMPORTANTE:</strong> Los materiales deben cumplir con las especificaciones técnicas homologadas por CESSA para ser aprobados en la inspección.</p>
        <p>Solicite también su nueva conexión en línea a través de: <strong>https://www.cessa.com.bo/nueva-conexion</strong></p>
        <p><i>CESSA - Energía que impulsa a Chuquisaca</i></p>
    </div>
</div>

</body>
</html>
