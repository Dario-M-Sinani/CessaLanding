<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'title',
        'alias',
        'description',
        'published',
        'created_by',
        'modified_by',
    ];

    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}
