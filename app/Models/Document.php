<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'title',
        'url',
        'position',
        'publication_id',
        'published',
        'created_by',
        'modified_by',
    ];

    public function publication()
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }
}
