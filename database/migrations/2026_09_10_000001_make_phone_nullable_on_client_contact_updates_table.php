<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La página pública "Actualizar Datos" vuelve al sitio institucional pidiendo solo
     * N° de Cliente + N° de Cuenta y confirmando únicamente el correo (sin SMS/celular,
     * a diferencia de la versión original de agosto) -- ver App\Http\Controllers\
     * ActualizarDatosController. `phone` deja de llenarse en registros nuevos.
     */
    public function up(): void
    {
        Schema::table('client_contact_updates', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('client_contact_updates', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->change();
        });
    }
};
