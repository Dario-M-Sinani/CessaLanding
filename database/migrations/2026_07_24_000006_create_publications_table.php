<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('publications')) {
            Schema::create('publications', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->date('expired_date')->nullable();
                $table->enum('type', ['BIDDING', 'INVITATION', 'ANNOUNCEMENT', 'ASSETS_SALES', 'OTHERS'])->default('OTHERS');
                $table->string('published', 1)->default('S');
                $table->string('created_by', 30)->nullable();
                $table->string('modified_by', 30)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
