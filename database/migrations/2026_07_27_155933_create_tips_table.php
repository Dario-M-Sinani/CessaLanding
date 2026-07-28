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
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->integer('position')->default(0);
            $table->enum('tip_type', ['TEXT', 'VIDEO'])->default('TEXT');
            $table->string('published', 1)->default('S');
            $table->string('created_by', 30)->nullable();
            $table->string('modified_by', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
