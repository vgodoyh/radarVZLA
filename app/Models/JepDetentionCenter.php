<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JepDetentionCenter extends Model
{
    protected $fillable = ['snapshot_id', 'name', 'value', 'sort_order'];
    protected function casts(): array { return ['value' => 'integer', 'sort_order' => 'integer']; }
    public function snapshot(): BelongsTo { return $this->belongsTo(JepMetricSnapshot::class, 'snapshot_id'); }
}
