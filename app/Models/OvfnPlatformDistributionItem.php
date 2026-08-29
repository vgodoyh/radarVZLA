<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvfnPlatformDistributionItem extends Model
{
    protected $fillable = ['distribution_id', 'platform', 'value', 'sort_order'];

    protected function casts(): array { return ['value' => 'integer', 'sort_order' => 'integer']; }

    public function distribution(): BelongsTo { return $this->belongsTo(OvfnPlatformDistribution::class, 'distribution_id'); }
}
