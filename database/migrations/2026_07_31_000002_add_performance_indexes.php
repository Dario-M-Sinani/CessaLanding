<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->index('status');
            $table->index('send_date');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->index(['published', 'created_at']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index(['published', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['send_date']);
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex(['published', 'created_at']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['published', 'created_at']);
        });
    }
};
