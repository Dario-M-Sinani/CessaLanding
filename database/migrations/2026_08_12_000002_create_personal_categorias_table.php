<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            // Slug usado en la URL pública (/personal/{alias}) y en Personal.categoria hasta
            // ahora -- se mantiene editable desde el panel (PersonalCategoriaResource).
            $table->string('alias', 60)->unique();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Sembradas con los mismos 3 alias que ya usaba la constante fija Personal::CATEGORIAS
        // -- la migración siguiente (add_personal_categoria_id_to_personal_table) los usa para
        // resolver el `categoria` (string) que ya tienen las 32 personas reales cargadas.
        DB::table('personal_categorias')->insert([
            ['nombre' => 'Personal Autorizado', 'alias' => 'autorizado', 'position' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Personal Externo - Cortes y Reconexiones', 'alias' => 'cortes-reconexiones', 'position' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Personal Externo - Lectura de Medidores', 'alias' => 'lectura-medidores', 'position' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_categorias');
    }
};
