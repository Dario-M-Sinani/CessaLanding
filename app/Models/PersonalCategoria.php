<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalCategoria extends Model
{
    protected $table = 'personal_categorias';

    protected $fillable = [
        'nombre',
        'alias',
        'position',
    ];

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class, 'personal_categoria_id');
    }
}
