<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            // Solo se llena para los QR que dispara el propio cliente desde Consulta de
            // Deuda (PagoQrController) -- los cobros que arma el staff a mano desde el panel
            // (ListRecibos) no están atados a un abonado, así que queda nullable.
            $table->string('nro_cliente', 10)->nullable()->after('glosa')->index();

            // Antes era `date` (alcanza para el vencimiento a fin de día que arma SIP en el
            // flujo del panel), pero el QR de Consulta de Deuda ahora vence a los 5 minutos
            // (ver PagoQrController y el comando pagos:expirar-vencidos) y eso no se puede
            // representar con precisión de solo día.
            $table->timestamp('expires_at')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropColumn('nro_cliente');
            $table->date('expires_at')->nullable(false)->change();
        });
    }
};
