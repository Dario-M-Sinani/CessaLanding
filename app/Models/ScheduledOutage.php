<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledOutage extends Model
{
    use HasFactory;

    protected $table = 'scheduled_outages';

    protected $fillable = [
        'reason',
        'location',
        'execution_date',
        'start_time',
        'finish_time',
        'published',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'execution_date' => 'date',
    ];
}
