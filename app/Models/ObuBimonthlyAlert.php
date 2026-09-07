<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObuBimonthlyAlert extends Model
{
    protected $fillable = [
        'organization_id', 'title', 'excerpt', 'url', 'image_path', 'file_path',
        'file_name', 'mime_type', 'file_size', 'period_start', 'period_end',
        'is_published', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'is_published' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
