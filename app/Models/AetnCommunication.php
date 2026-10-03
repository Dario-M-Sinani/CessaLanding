<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AetnCommunication extends Model
{
    use HasFactory;

    protected $table = 'aetn_communications';

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'document_url',
        'published_date',
        'published',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];
}
