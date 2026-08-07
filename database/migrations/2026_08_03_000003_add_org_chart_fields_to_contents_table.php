<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('org_chart_image', 350)->nullable()->after('show_image');
            $table->string('pei_document', 350)->nullable()->after('org_chart_image');
            $table->boolean('show_org_chart')->default(true)->after('pei_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['org_chart_image', 'pei_document', 'show_org_chart']);
        });
    }
};
