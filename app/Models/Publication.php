<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publication extends Model
{
    protected $fillable = ['organization_id', 'source', 'external_id', 'category', 'title', 'excerpt', 'url', 'image_url', 'likes', 'shares', 'published_at', 'metadata'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'metadata' => 'array'];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
