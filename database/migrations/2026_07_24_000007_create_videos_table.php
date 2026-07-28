<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('videos')) {
            Schema::create('videos', function (Blueprint $table) {
                $table->id();
                $table->string('title', 180);
                $table->string('description', 240)->nullable();
                $table->string('url', 240);
                $table->integer('position')->default(0);
                $table->string('published', 1)->default('S');
                $table->string('created_by', 30)->nullable();
                $table->string('modified_by', 30)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
