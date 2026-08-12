<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Clave/valor genérico en la misma SQLite aislada que el respaldo de registros (conexión
 * `demo_registros`). Se lee en caliente en cada request (sin caché de config ni de .env),
 * así que actualizarla desde la página oculta de administración surte efecto inmediato, sin
 * reiniciar el servidor -- a diferencia de cambiar TIGO_SMS_TOKEN directo en .env.
 */
class DemoConfiguracion extends Model
{
    protected $connection = 'demo_registros';

    protected $table = 'configuraciones';

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['clave', 'valor', 'actualizado_en'];

    public static function obtener(string $clave, ?string $default = null): ?string
    {
        return static::query()->find($clave)?->valor ?? $default;
    }

    public static function guardar(string $clave, string $valor): void
    {
        static::query()->updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor, 'actualizado_en' => now()],
        );
    }
}
