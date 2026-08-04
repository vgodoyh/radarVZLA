<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['slug', 'name', 'x_username', 'website_url', 'logo_path', 'x_logo_path', 'color', 'position', 'active', 'last_synced_at'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'last_synced_at' => 'datetime'];
    }

    /** @return HasMany<Publication, $this> */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
