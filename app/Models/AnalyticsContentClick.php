<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int|string $home_clicks
 * @property-read int|string $organization_clicks
 * @property-read int|string $total_clicks
 */
class AnalyticsContentClick extends Model
{
    protected $fillable = [
        'organization',
        'content_type',
        'content_id',
        'source',
        'session_id',
    ];
}
