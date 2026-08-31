<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            // Snapshot exacto de los ítems de deuda que devolvió SIIC al momento de generar el
            // QR (codigo_sucursal, nro_comprobante, nro_suministro, fecha, tipo, letra_comprobante,
            // nro_autorizacion, nro_cliente, anio, mes, importe, detalle) -- PagoQrController ya
            // calculaba esto para el monto/glosa pero no lo guardaba. Hace falta tal cual para
            // "Pagar Transacción" en api-cobranzas-bancos una vez que el pago se confirma.
            $table->json('debt_items')->nullable()->after('descripcion_pago');

            // Datos de la Transacción registrada en api-cobranzas-bancos (SIIC) -- ver
            // CobranzasBancoService y RegistrarFacturacionCobranzas.
            $table->uuid('cobranzas_uuid')->nullable()->after('debt_items');
            $table->string('comprobante_path')->nullable()->after('cobranzas_uuid');
            $table->timestamp('facturado_at')->nullable()->after('comprobante_path');
            $table->unsignedTinyInteger('facturacion_intentos')->default(0)->after('facturado_at');
            $table->text('facturacion_error')->nullable()->after('facturacion_intentos');
        });
    }

    public function down(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropColumn([
                'debt_items',
                'cobranzas_uuid',
                'comprobante_path',
                'facturado_at',
                'facturacion_intentos',
                'facturacion_error',
            ]);
        });
    }
};
