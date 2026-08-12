<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Misma conexión aislada `demo_registros` que la tabla `registros` (ver esa migración para
 * el porqué de fijar $connection acá). Tabla clave/valor genérica -- hoy solo se usa para
 * `tigo_sms_token` (actualizable desde la página oculta de administración sin reiniciar el
 * servidor, a diferencia de TIGO_SMS_TOKEN en .env), pero sirve para cualquier otro valor
 * operativo que la herramienta necesite cambiar en caliente más adelante.
 */
return new class extends Migration
{
    protected $connection = 'demo_registros';

    public function up(): void
    {
        Schema::connection('demo_registros')->create('configuraciones', function (Blueprint $table) {
            $table->string('clave')->primary();
            $table->text('valor')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('demo_registros')->dropIfExists('configuraciones');
    }
};
