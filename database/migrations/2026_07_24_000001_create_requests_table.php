<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('requests')) {
            Schema::create('requests', function (Blueprint $table) {
                $table->id();
                $table->integer('code_number')->nullable();
                $table->integer('code_numer_siic')->nullable();
                $table->dateTime('send_date')->nullable();
                $table->string('fullname', 180);
                $table->string('email', 180);
                $table->string('user_type', 50)->default('RESIDENCIAL');
                $table->string('service_type', 100)->nullable();
                $table->string('area', 100)->nullable();
                $table->string('consumer_type', 100)->nullable();
                $table->string('document_number', 30);
                $table->string('url_document_front', 255)->nullable();
                $table->string('url_document_back', 255)->nullable();
                $table->string('url_invoice', 255)->nullable();
                $table->string('mobile_phone', 30);
                $table->string('phone', 30)->nullable();
                $table->string('address', 150);
                $table->string('zone', 150);
                $table->text('reference')->nullable();
                $table->double('longitude', 10, 6)->nullable();
                $table->double('latitude', 10, 6)->nullable();
                $table->integer('last_meter_reading')->nullable();
                $table->string('url_last_meter_reading', 255)->nullable();
                $table->string('status', 30)->default('PENDIENTE');
                $table->text('observation')->nullable();
                $table->unsignedBigInteger('request_form_id')->nullable();
                $table->unsignedBigInteger('request_type_id')->nullable();
                $table->string('created_by', 50)->nullable();
                $table->string('modified_by', 50)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
