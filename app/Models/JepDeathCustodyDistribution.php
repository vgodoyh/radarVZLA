<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JepDeathCustodyDistribution extends Model
{
    protected $fillable = ['snapshot_id', 'category_key', 'label', 'value', 'sort_order'];

    protected function casts(): array
    {
        return ['value' => 'integer', 'sort_order' => 'integer'];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(JepMetricSnapshot::class, 'snapshot_id');
    }
}
