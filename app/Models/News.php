<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'alias',
        'summary',
        'full_text',
        'image_url',
        'images',
        'tags',
        'hits',
        'popup',
        'published',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'popup' => 'boolean',
        'images' => 'array',
    ];
}
