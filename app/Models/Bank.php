<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'banks';

    protected $fillable = [
        'name',
        'url',
        'img_url',
        'published',
        'created_by',
        'modified_by',
    ];

    public function collectionsPoints()
    {
        return $this->hasMany(CollectionsPoint::class);
    }
}
