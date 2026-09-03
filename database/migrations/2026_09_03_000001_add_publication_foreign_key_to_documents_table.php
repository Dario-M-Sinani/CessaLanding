<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // documents.publication_id nunca tuvo una FK real -- borrar una Publication
        // dejaba sus Document huérfanos (visibles para siempre en /informacion/documentos,
        // ver ESTADO_SEGURIDAD_MIGRACION.md). cascadeOnDelete() hace que la base misma
        // los borre, sin depender de que cada camino de borrado (panel, Tinker, comando)
        // se acuerde de hacerlo a mano.
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('publication_id')
                ->references('id')->on('publications')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['publication_id']);
        });
    }
};
