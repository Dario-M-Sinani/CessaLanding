<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scheduled_outages')) {
            Schema::create('scheduled_outages', function (Blueprint $table) {
                $table->id();
                $table->text('reason');
                $table->text('location');
                $table->date('execution_date');
                $table->time('start_time');
                $table->time('finish_time');
                $table->string('published', 1)->default('S');
                $table->string('created_by', 30)->nullable();
                $table->string('modified_by', 30)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_outages');
    }
};
