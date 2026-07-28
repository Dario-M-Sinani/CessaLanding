<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contents')) {
            Schema::create('contents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('title', 240);
                $table->string('alias', 240)->unique();
                $table->text('summary')->nullable();
                $table->longText('full_text');
                $table->integer('hits')->default(0);
                $table->integer('position')->default(0);
                $table->string('published', 1)->default('S');
                $table->string('created_by', 30)->nullable();
                $table->string('modified_by', 30)->nullable();
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
