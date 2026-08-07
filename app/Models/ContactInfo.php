<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $table = 'contact_info';

    protected $fillable = [
        'address',
        'latitude',
        'longitude',
        'show_map',
        'phones',
        'schedules',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'show_map' => 'boolean',
        'phones' => 'array',
        'schedules' => 'array',
    ];
}
