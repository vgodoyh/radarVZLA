<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObuMonthlyNote extends Model
{
    protected $fillable = [
        'organization_id', 'title', 'excerpt', 'image_path', 'publication_date',
        'is_published', 'url', 'user_id',
    ];

    protected function casts(): array
    {
        return ['publication_date' => 'date', 'is_published' => 'boolean'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
