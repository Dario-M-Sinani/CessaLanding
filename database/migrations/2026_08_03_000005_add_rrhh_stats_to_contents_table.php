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
            $table->json('staff_yearly_stats')->nullable()->after('show_org_chart');
            $table->json('gender_yearly_stats')->nullable()->after('staff_yearly_stats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['staff_yearly_stats', 'gender_yearly_stats']);
        });
    }
};
