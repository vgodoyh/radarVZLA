<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsPageView extends Model
{
    protected $fillable = [
        'organization',
        'page',
        'source',
        'session_id',
        'ip_hash',
        'user_agent',
    ];
}
