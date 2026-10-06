<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `expires_at` quedó como `timestamp NOT NULL` (ver 2026_08_11_095523). En MySQL/MariaDB con
     * explicit_defaults_for_timestamp=OFF, la primera columna TIMESTAMP NOT NULL de la tabla
     * recibe sola `DEFAULT current_timestamp() ON UPDATE current_timestamp()`: cada UPDATE del
     * recibo (Inhabilitado, Expirado, Pagado, Facturado...) le pisaba el vencimiento con la hora
     * de la sesión de MySQL (America/La_Paz en local, no UTC como el resto de la app). Como
     * DATETIME no se autoactualiza nunca ni convierte zonas horarias, conserva tal cual lo que
     * escribe Laravel. Los valores que ya existen se mantienen (se convierten como se ven).
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE recibos MODIFY expires_at DATETIME NOT NULL');
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE recibos MODIFY expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }
};
