<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsNavigationClick extends Model
{
    protected $fillable = [
        'organization',
        'target',
        'source',
        'session_id',
    ];
}
