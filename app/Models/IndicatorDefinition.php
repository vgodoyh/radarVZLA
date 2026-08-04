<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndicatorDefinition extends Model
{
    protected $fillable = ['organization_id', 'key', 'label_es', 'label_en', 'unit', 'icon', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return HasMany<IndicatorValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(IndicatorValue::class);
    }
}
