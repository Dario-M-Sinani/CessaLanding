<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Monofásico / Trifásico -- distinto de `service_type`, que en este puerto
            // identifica el tipo de trámite (nueva conexión / suspensión / otras).
            $table->string('phase_type', 20)->nullable()->after('consumer_type');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('phase_type');
        });
    }
};
