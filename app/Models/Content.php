<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $table = 'contents';

    protected $fillable = [
        'category_id',
        'title',
        'alias',
        'summary',
        'full_text',
        'hits',
        'position',
        'published',
        'created_by',
        'modified_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
