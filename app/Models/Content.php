<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $table = 'contents';

    protected $fillable = [
        'category_id',
        'title',
        'alias',
        'summary',
        'full_text',
        'image_url',
        'show_image',
        'org_chart_image',
        'pei_document',
        'show_org_chart',
        'staff_yearly_stats',
        'gender_yearly_stats',
        'hits',
        'position',
        'published',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'show_image' => 'boolean',
        'show_org_chart' => 'boolean',
        'staff_yearly_stats' => 'array',
        'gender_yearly_stats' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
