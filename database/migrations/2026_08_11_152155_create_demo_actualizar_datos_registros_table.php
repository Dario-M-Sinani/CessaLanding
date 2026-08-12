<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corre contra la conexión `demo_registros` (SQLite aparte, ver config/database.php), nunca
 * contra `mysql` (cessa_bdweb) -- por eso NO usa `Schema::` sin conexión ni el helper
 * `$this->connection` implícito de `php artisan migrate` normal, sino que fija
 * `$connection` explícitamente para que corra ahí sin importar cuál sea la conexión por
 * defecto del .env.
 */
return new class extends Migration
{
    protected $connection = 'demo_registros';

    public function up(): void
    {
        Schema::connection('demo_registros')->create('registros', function (Blueprint $table) {
            $table->id();
            $table->string('nro_cliente', 20);
            $table->string('cuenta', 20);
            $table->string('nombre')->nullable();
            $table->string('email');
            $table->string('phone', 20);
            $table->timestamp('capturado_en');
        });
    }

    public function down(): void
    {
        Schema::connection('demo_registros')->dropIfExists('registros');
    }
};
