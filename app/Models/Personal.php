<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    // Categorías fijas del dominio (calcan las 3 páginas /personal/* del legacy) --
    // no ameritan una tabla de categorías administrable como Content/Category, que
    // sirve a un menú dinámico distinto (Consumidor).
    public const CATEGORIAS = [
        'autorizado' => 'Personal Autorizado',
        'cortes-reconexiones' => 'Personal Externo - Cortes y Reconexiones',
        'lectura-medidores' => 'Personal Externo - Lectura de Medidores',
    ];

    protected $fillable = [
        'categoria',
        'nombre',
        'ci',
        'tipo_sangre',
        'celular',
        'descripcion',
        'foto',
        'published',
        'position',
        'created_by',
        'modified_by',
    ];
}
