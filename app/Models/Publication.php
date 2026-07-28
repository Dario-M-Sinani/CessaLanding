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
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'publication_id');
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
}
