<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aetn_communications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('image_url', 350)->nullable();
            $table->string('document_url', 350)->nullable();
            $table->date('published_date');
            $table->string('published', 1)->default('S');
            $table->string('created_by', 30)->nullable();
            $table->string('modified_by', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aetn_communications');
    }
};
