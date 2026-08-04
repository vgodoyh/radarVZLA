<?php

namespace App\Console\Commands;

use App\Jobs\SyncDashboardData;
use Illuminate\Console\Command;

class SyncDashboard extends Command
{
    protected $signature = 'dashboard:sync {--now : Execute synchronously}';

    protected $description = 'Synchronize the public dashboard sources';

    public function handle(): int
    {
        $this->option('now') ? SyncDashboardData::dispatchSync() : SyncDashboardData::dispatch();
        $this->components->info($this->option('now') ? 'Dashboard synchronized.' : 'Dashboard synchronization queued.');

        return self::SUCCESS;
    }
}
