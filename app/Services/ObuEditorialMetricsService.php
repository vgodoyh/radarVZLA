<?php

namespace App\Services;

use App\Models\ObuMonitoringPeriod;
use App\Models\Organization;
use Illuminate\Support\Facades\Schema;

class ObuEditorialMetricsService
{
    public function currentMetrics(): ?ObuMonitoringPeriod
    {
        if (! Schema::hasTable('obu_monitoring_periods') || ! Schema::hasTable('organizations')) {
            return null;
        }

        $organization = Organization::query()->where('slug', 'universidades')->first();

        return $organization
            ? ObuMonitoringPeriod::query()->current()->where('organization_id', $organization->id)->latest('valid_from')->first()
            : null;
    }
}
