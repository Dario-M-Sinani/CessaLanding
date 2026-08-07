<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $table = 'images';

    protected $fillable = [
        'title',
        'category',
        'url',
        'position',
        'published',
        'home_carousel',
        'home_carousel_mobile',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'home_carousel' => 'boolean',
        'home_carousel_mobile' => 'boolean',
    ];
}
