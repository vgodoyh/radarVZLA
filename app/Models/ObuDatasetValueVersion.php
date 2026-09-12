<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObuDatasetValueVersion extends Model
{
    protected $fillable = [
        'obu_dataset_value_id', 'organization_id', 'dataset_key', 'period_year',
        'category', 'subgroup', 'label', 'value', 'percentage', 'sort_order',
        'valid_from', 'valid_until', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'value' => 'integer',
            'percentage' => 'decimal:2',
            'sort_order' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function datasetValue(): BelongsTo
    {
        return $this->belongsTo(ObuDatasetValue::class, 'obu_dataset_value_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
