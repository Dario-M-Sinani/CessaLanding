<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            // Descripción legible completa, sin el límite de 30 caracteres que exige SIP para
            // `glosa` (ver PagoQrController) -- pensada para constancia interna/exportación,
            // nunca viaja al proveedor de pagos.
            $table->text('descripcion_pago')->nullable()->after('glosa');
        });
    }

    public function down(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropColumn('descripcion_pago');
        });
    }
};
