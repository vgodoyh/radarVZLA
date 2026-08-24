<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSyncRun extends Model
{
    protected $fillable = ['organization', 'process', 'status', 'started_at', 'finished_at', 'error', 'summary'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime', 'summary' => 'array'];
    }
}
