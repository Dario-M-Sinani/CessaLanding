<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_outages', function (Blueprint $table) {
            // 'programado' = mantenimiento anunciado con anticipación; 'emergencia' = corte
            // imprevisto (choque de poste, falla, clima) que se publica en cuanto se conoce.
            $table->string('type', 20)->default('programado')->after('id');
            // Solo emergencias: cuándo se repuso el servicio. Null = aún en atención.
            $table->dateTime('restored_at')->nullable()->after('finish_time');
            // En una emergencia la hora de reposición puede no conocerse aún.
            $table->time('finish_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_outages', function (Blueprint $table) {
            $table->dropColumn(['type', 'restored_at']);
        });
    }
};
