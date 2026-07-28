<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CessaRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'code_number',
        'code_numer_siic',
        'send_date',
        'fullname',
        'email',
        'user_type',
        'service_type',
        'area',
        'consumer_type',
        'document_number',
        'url_document_front',
        'url_document_back',
        'url_invoice',
        'mobile_phone',
        'phone',
        'address',
        'zone',
        'reference',
        'longitude',
        'latitude',
        'last_meter_reading',
        'url_last_meter_reading',
        'status',
        'observation',
        'request_form_id',
        'request_type_id',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'send_date' => 'datetime',
        'longitude' => 'float',
        'latitude' => 'float',
    ];
}
