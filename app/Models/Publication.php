<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $table = 'publications';

    protected $fillable = [
        'title',
        'description',
        'expired_date',
        'type',
        'published',
        'created_by',
        'modified_by',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'publication_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public static function getTypes(): array
    {
        return [
            'BIDDING' => 'Licitación',
            'INVITATION' => 'Invitación',
            'ANNOUNCEMENT' => 'Convocatoria',
            'ASSETS_SALES' => 'Remate de Activos',
            'OTHERS' => 'Otros',
        ];
    }

    // Agrupación pública de los tipos de proceso, usada en Inicio y en el filtro
    // de /procesos. Convocatoria y Remate de Activos se muestran juntos como
    // "Convocatorias y Pujas" porque para el usuario final son la misma idea.
    public static function getGroups(): array
    {
        return [
            'invitaciones' => ['INVITATION'],
            'licitaciones' => ['BIDDING'],
            'convocatorias_pujas' => ['ANNOUNCEMENT', 'ASSETS_SALES'],
            'documentos' => ['OTHERS'],
        ];
    }

    public static function getGroupLabels(): array
    {
        return [
            'invitaciones' => 'Invitaciones',
            'licitaciones' => 'Licitaciones',
            'convocatorias_pujas' => 'Convocatorias y Pujas',
            'documentos' => 'Documentos',
        ];
    }

    public static function countsByGroup(): array
    {
        return collect(self::getGroups())
            ->map(fn (array $types) => self::where('published', 'S')->whereIn('type', $types)->count())
            ->all();
    }
}
