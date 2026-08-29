<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OvfnPlatformDistribution extends Model
{
    protected $fillable = ['organization_id', 'data_from_date', 'valid_from', 'valid_until', 'user_id'];

    protected function casts(): array
    {
        return [
            'data_from_date' => 'date',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function items(): HasMany { return $this->hasMany(OvfnPlatformDistributionItem::class, 'distribution_id')->orderBy('sort_order'); }

    public function scopeCurrent(Builder $query): Builder { return $query->whereNull('valid_until'); }
}
