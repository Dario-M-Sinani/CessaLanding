<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contact_updates', function (Blueprint $table) {
            $table->id();

            // Identifica al abonado ya verificado contra SIIC (nro_cliente + N° de Cuenta
            // "zona-manzano-correlativo", el mismo doble factor que usa Consulta de Deuda).
            // SIIC no tiene un endpoint de escritura conocido hoy, así que esta tabla es la
            // fuente que el personal de Atención al Cliente revisa para aplicar el cambio
            // manualmente en SIIC -- ver App\Http\Controllers\ActualizarDatosController.
            $table->string('nro_cliente', 10);
            $table->string('cuenta', 20);
            $table->string('client_name')->nullable();

            $table->string('email');
            $table->string('phone', 20);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->timestamps();

            $table->unique(['nro_cliente', 'cuenta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contact_updates');
    }
};
