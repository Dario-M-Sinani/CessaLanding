<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // `integer` se queda corto para lecturas de hasta 12 dígitos que pide el
        // formulario de Suspensión (máx. ~10 dígitos en un INT con signo).
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('last_meter_reading');
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->string('last_meter_reading', 12)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('last_meter_reading');
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->integer('last_meter_reading')->nullable()->after('latitude');
        });
    }
};
