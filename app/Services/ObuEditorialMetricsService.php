<?php

namespace App\Services;

use App\Models\ObuMetricSnapshot;
use App\Models\Organization;
use Illuminate\Support\Facades\Schema;

class ObuEditorialMetricsService
{
    public function currentMetrics(): ?ObuMetricSnapshot
    {
        if (! Schema::hasTable('obu_metric_snapshots') || ! Schema::hasTable('organizations')) {
            return null;
        }

        $organization = Organization::query()->where('slug', 'universidades')->first();

        return $organization
            ? ObuMetricSnapshot::query()->current()->where('organization_id', $organization->id)->first()
            : null;
    }
}
