<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Respaldo aislado de lo que recolecta la herramienta aparte (Vite/Vue, fuera de este repo
 * en `documentosss/CESSA/herramienta-actualizar-datos/`) -- vive en su propia base SQLite
 * (conexión `demo_registros`, ver config/database.php), nunca en `cessa_bdweb`. El registro
 * "real" para el sitio institucional sigue siendo `ClientContactUpdate`; este modelo es
 * solo la copia de respaldo para que el localStorage del navegador que recolecta no sea la
 * única copia que existe.
 */
class DemoActualizarDatosRegistro extends Model
{
    protected $connection = 'demo_registros';

    protected $table = 'registros';

    public $timestamps = false;

    protected $fillable = [
        'nro_cliente',
        'cuenta',
        'nombre',
        'email',
        'phone',
        'capturado_en',
    ];

    protected $casts = [
        'capturado_en' => 'datetime',
    ];
}
