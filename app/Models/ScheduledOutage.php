<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledOutage extends Model
{
    use HasFactory;

    protected $table = 'scheduled_outages';

    public const TYPE_PROGRAMADO = 'programado';

    public const TYPE_EMERGENCIA = 'emergencia';

    // Tras reponerse el servicio, la emergencia sigue visible en el inicio este tiempo
    // (en verde, "servicio restablecido") y luego desaparece sola.
    public const RESTORED_VISIBLE_HOURS = 6;

    // Tope duro: una emergencia solo se muestra en el inicio durante las 24 h siguientes a su
    // publicación (created_at), sin importar la fecha/horas de inicio y fin que se carguen.
    public const EMERGENCY_VISIBLE_HOURS = 24;

    protected $fillable = [
        'type',
        'reason',
        'location',
        'affected_institutions',
        'execution_date',
        'start_time',
        'finish_time',
        'restored_at',
        'published',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'execution_date' => 'date',
        'restored_at' => 'datetime',
    ];

    public function scopeProgramados($query)
    {
        return $query->where('type', self::TYPE_PROGRAMADO);
    }

    /** Emergencias publicadas hace menos de 24 h, aún en atención o repuestas hace poco. */
    public function scopeEmergenciasVisibles($query)
    {
        return $query->where('type', self::TYPE_EMERGENCIA)
            ->where('published', 'S')
            ->where('created_at', '>=', now()->subHours(self::EMERGENCY_VISIBLE_HOURS))
            ->where(fn ($q) => $q->whereNull('restored_at')
                ->orWhere('restored_at', '>=', now()->subHours(self::RESTORED_VISIBLE_HOURS)));
    }
}
