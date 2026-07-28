<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->string('title', 240);
                $table->string('url', 350);
                $table->integer('position')->default(0);
                $table->unsignedBigInteger('publication_id')->nullable();
                $table->string('published', 1)->default('S');
                $table->string('created_by', 30)->nullable();
                $table->string('modified_by', 30)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
