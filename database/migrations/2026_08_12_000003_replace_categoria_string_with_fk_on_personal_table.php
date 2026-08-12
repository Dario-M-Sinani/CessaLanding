<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->foreignId('personal_categoria_id')->nullable()->after('categoria')
                ->constrained('personal_categorias')->nullOnDelete();
        });

        // Backfill: `categoria` (string) tenía el mismo alias que ahora vive en
        // personal_categorias (sembrada en la migración anterior con esos 3 valores) --
        // había 32 personas reales cargadas al momento de escribir esto, ninguna se pierde.
        $categorias = DB::table('personal_categorias')->pluck('id', 'alias');
        foreach ($categorias as $alias => $id) {
            DB::table('personal')->where('categoria', $alias)->update(['personal_categoria_id' => $id]);
        }

        Schema::table('personal', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->string('categoria', 40)->nullable()->after('personal_categoria_id');
        });

        $aliases = DB::table('personal_categorias')->pluck('alias', 'id');
        foreach ($aliases as $id => $alias) {
            DB::table('personal')->where('personal_categoria_id', $id)->update(['categoria' => $alias]);
        }

        Schema::table('personal', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_categoria_id');
            $table->string('categoria', 40)->nullable(false)->change();
        });
    }
};
