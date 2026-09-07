<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObuDatasetValue extends Model
{
    protected $fillable = [
        'organization_id', 'dataset_key', 'period_year', 'category', 'subgroup',
        'label', 'value', 'percentage', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'value' => 'integer',
            'percentage' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
