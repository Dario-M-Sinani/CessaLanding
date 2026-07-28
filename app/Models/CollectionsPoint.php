<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionsPoint extends Model
{
    use HasFactory;

    protected $table = 'collections_points';

    protected $fillable = [
        'name',
        'address',
        'phones',
        'latitude',
        'longitude',
        'attention_schedule',
        'bank_id',
        'published',
        'created_by',
        'modified_by',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
