<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 40);
            $table->string('nombre', 150);
            $table->string('ci', 20);
            $table->string('tipo_sangre', 10)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('foto', 300)->nullable();
            $table->string('published', 1)->default('S');
            $table->integer('position')->default(0);
            $table->string('created_by', 30)->nullable();
            $table->string('modified_by', 30)->nullable();
            $table->timestamps();

            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
