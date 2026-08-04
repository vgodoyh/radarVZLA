<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorValue extends Model
{
    protected $fillable = ['indicator_definition_id', 'period_start', 'period_end', 'value', 'change_percentage', 'source_url', 'verified_at'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'verified_at' => 'datetime', 'value' => 'decimal:2', 'change_percentage' => 'decimal:2'];
    }

    /** @return BelongsTo<IndicatorDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(IndicatorDefinition::class, 'indicator_definition_id');
    }
}
