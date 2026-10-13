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

        @media screen and (max-width: 480px) {
            body {
                padding: 12px;
            }
            .sheet {
                padding: 16px;
            }
        }

        .no-print-bar {
            max-width: 680px;
            margin: 0 auto 15px auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .btn {
            white-space: nowrap;
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

        /* Etiqueta (4.10" x 1.70"): en pantalla se ve como vista previa a tamaño real; al imprimir
           es lo único que sale, una etiqueta por página. Hasta 10 materiales por etiqueta en 2
           columnas, con nombre corto y casilla para tildar (antes: 5 por etiqueta en letra de 6 pt,
           2 etiquetas medio vacías por tipo). El corte lo arma el servidor (array_chunk). */
        .preview {
            max-width: 680px;
            margin: 0 auto 18px auto;
            overflow-x: auto;
        }
        .preview-titulo {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444;
            margin-bottom: 6px;
        }
        .preview .etiqueta {
            flex-shrink: 0;
            border: 1px dashed #888;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
        }
        .etiqueta {
            width: 4.10in;
            height: 1.70in;
            padding: 0.07in 0.13in 0.05in 0.13in;
            overflow: hidden;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
        }
        .etiqueta .cabecera {
            border-bottom: 1.5px solid #000;
            padding-bottom: 2px;
        }
        .etiqueta .linea-marca {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.08in;
        }
        .etiqueta .marca {
            white-space: nowrap;
            font-size: 6.5px;
            font-weight: 900;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .etiqueta .titulo {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.1;
        }
        .etiqueta .variante {
            font-size: 7.5px;
            font-weight: 900;
            white-space: nowrap;
            text-align: right;
        }
        .etiqueta .items {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-auto-flow: column;
            column-gap: 0.12in;
            padding: 2px 0 1px 0;
            min-height: 0;
        }
        .etiqueta .item {
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 8.5px;
            line-height: 1.1;
            border-bottom: 0.5px dashed #999;
            min-width: 0;
        }
        .etiqueta .check {
            flex: 0 0 auto;
            width: 7px;
            height: 7px;
            border: 1px solid #000;
        }
        .etiqueta .cant {
            flex: 0 0 0.36in;
            font-weight: 900;
            white-space: nowrap;
        }
        .etiqueta .pie {
            display: flex;
            justify-content: space-between;
            gap: 0.08in;
            border-top: 1px solid #000;
            padding-top: 1px;
            font-size: 6.5px;
            font-weight: bold;
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
            .no-print-bar,
            .sheet,
            .preview-titulo {
                display: none !important;
            }
            .preview {
                max-width: none;
                margin: 0;
                overflow: visible;
            }
            .preview .etiqueta {
                border: none;
                box-shadow: none;
                margin: 0;
                break-after: page;
                page-break-after: always;
            }
            .preview .etiqueta:last-child {
                break-after: auto;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

@php
    $porEtiqueta = 10;
    $grupos = array_chunk($materiales, $porEtiqueta);
    $varianteLabel = $instalacion['variantes'][$variante]['label'] ?? '';
@endphp

<!-- Barra superior de acciones (No se imprime) -->
<div class="no-print-bar">
    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
        <label for="tipoSelect" style="font-size: 12px; font-weight: bold;">Tipo:</label>
        <select id="tipoSelect" class="select-tipo" onchange="location.href='/instalaciones/imprimir/' + this.value + '?variante={{ $variante }}'">
            <option value="monofasica" {{ $instalacion['id'] === 'monofasica' ? 'selected' : '' }}>Monofásica (220V)</option>
            <option value="trifasica" {{ $instalacion['id'] === 'trifasica' ? 'selected' : '' }}>Trifásica (380V)</option>
            <option value="tablero-centralizador" {{ $instalacion['id'] === 'tablero-centralizador' ? 'selected' : '' }}>Tablero Centralizador (3+)</option>
        </select>
        <select id="varianteSelect" class="select-tipo" onchange="location.href='/instalaciones/imprimir/{{ $instalacion['id'] }}?variante=' + this.value">
            @foreach($instalacion['variantes'] as $k => $v)
            <option value="{{ $k }}" {{ $variante === $k ? 'selected' : '' }}>{{ $v['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn">
            IMPRIMIR {{ count($grupos) > 1 ? count($grupos) . ' ETIQUETAS' : 'ETIQUETA' }}
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            CERRAR
        </button>
    </div>
</div>

<!-- Etiqueta(s): vista previa en pantalla, y lo único que se imprime (ver @media print) -->
<div class="preview">
    <div class="preview-titulo">Vista previa de la etiqueta (tamaño real)</div>
    @foreach($grupos as $n => $grupo)
    <div class="etiqueta">
        <div class="cabecera">
            <div class="linea-marca">
                <span class="marca">CESSA · Lista de materiales</span>
                <span class="variante">{{ $varianteLabel }}</span>
            </div>
            <div class="titulo">{{ $instalacion['titulo'] }}</div>
        </div>
        <div class="items" style="grid-template-rows: repeat({{ max(1, (int) ceil(count($grupo) / 2)) }}, 1fr);">
            @foreach($grupo as $m)
            <div class="item"><span class="check"></span><span class="cant">{{ $m['cantidad'] }}</span><span>{{ $m['corto'] ?? $m['item'] }}</span></div>
            @endforeach
        </div>
        <div class="pie">
            <span>Materiales homologados por CESSA · Solicitud en línea: cessa.com.bo/nueva-conexion</span>
            <span>{{ count($grupos) > 1 ? 'Etiqueta ' . ($n + 1) . ' de ' . count($grupos) : count($materiales) . ' materiales' }}</span>
        </div>
    </div>
    @endforeach
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
        <p>{{ $instalacion['subtitulo'] }} · {{ $varianteLabel }}</p>
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
            @foreach($materiales as $m)
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
