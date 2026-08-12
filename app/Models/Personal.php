<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'personal_categoria_id',
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

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(PersonalCategoria::class, 'personal_categoria_id');
    }
}
