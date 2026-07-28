<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    use HasFactory;

    protected $table = 'tips';

    protected $fillable = [
        'text',
        'position',
        'tip_type',
        'published',
        'created_by',
        'modified_by',
    ];
}
