<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Documentos adjuntos administrables (título + archivo) sin tocar el HTML de
            // full_text a mano -- antes la única forma de agregar un documento era editando el
            // HTML crudo dentro del RichEditor, que no tiene un botón para eso. Se renderizan
            // con el mismo componente de tarjetas (DocumentLinks.vue) que ya usaba
            // ContentBody.vue para las tablas de links migradas del legacy (ver §3.27).
            $table->json('documentos')->nullable()->after('full_text');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('documentos');
        });
    }
};
