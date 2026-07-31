<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'phase_type',
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

    protected $appends = [
        'formatted_code',
    ];

    // Prefijo del código visible (N-000001, S-000001, O-000001) según el tipo de trámite.
    // Cada prefijo lleva su propia numeración correlativa, independiente de las otras.
    public const CODE_PREFIX_GROUPS = [
        'N' => ['NUEVO_SUMINISTRO'],
        'S' => ['SUSPENSION_TEMPORAL', 'SUSPENSION_DEFINITIVA', 'SUSPENSION_INSPECCION'],
        'O' => ['OTRAS_SOLICITUDES'],
    ];

    public static function codePrefixFor(?string $serviceType): ?string
    {
        foreach (self::CODE_PREFIX_GROUPS as $prefix => $types) {
            if (in_array($serviceType, $types, true)) {
                return $prefix;
            }
        }

        return null;
    }

    protected static function booted(): void
    {
        static::creating(function (CessaRequest $request) {
            if ($request->code_number) {
                return;
            }

            $prefix = static::codePrefixFor($request->service_type);

            if (!$prefix) {
                return;
            }

            $types = self::CODE_PREFIX_GROUPS[$prefix];

            DB::transaction(function () use ($request, $types) {
                $max = static::query()
                    ->whereIn('service_type', $types)
                    ->lockForUpdate()
                    ->max('code_number');

                $request->code_number = ($max ?? 0) + 1;
            });
        });
    }

    public function getFormattedCodeAttribute(): ?string
    {
        if (!$this->code_number) {
            return null;
        }

        $prefix = static::codePrefixFor($this->service_type) ?? '?';

        return sprintf('%s-%06d', $prefix, $this->code_number);
    }
}
