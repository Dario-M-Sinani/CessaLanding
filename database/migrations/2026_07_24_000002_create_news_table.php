<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->id();
                $table->string('title', 180);
                $table->string('alias', 180)->nullable();
                $table->text('summary');
                $table->longText('full_text');
                $table->string('tags', 150)->nullable();
                $table->integer('hits')->default(0);
                $table->boolean('popup')->default(false);
                $table->string('published', 1)->default('S');
                $table->string('created_by', 30)->nullable();
                $table->string('modified_by', 30)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
