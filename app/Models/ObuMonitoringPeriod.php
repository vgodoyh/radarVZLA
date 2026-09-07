<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObuMonitoringPeriod extends Model
{
    protected $fillable = [
        'organization_id', 'period_start', 'period_end', 'analyzed_information',
        'protests', 'complaints', 'data_date', 'valid_from', 'valid_until', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'analyzed_information' => 'integer',
            'protests' => 'integer',
            'complaints' => 'integer',
            'data_date' => 'date',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
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

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('valid_until');
    }
}
