<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OvfnPlatformDistribution;
use App\Models\OvfnVerificationTotal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class OvfnEditorialMetricsService
{
    public const PLATFORMS = ['tiktok', 'whatsapp', 'x', 'instagram', 'facebook'];

    public function currentVerificationTotal(): OvfnVerificationTotal
    {
        $organization = Schema::hasTable('organizations')
            ? Organization::query()->where('slug', 'fake-news')->first()
            : null;
        $current = Schema::hasTable('ovfn_verification_totals') && $organization
            ? OvfnVerificationTotal::query()->current()->where('organization_id', $organization->id)->first()
            : null;

        return $current ?: new OvfnVerificationTotal(['total' => 137, 'data_date' => '2026-07-31']);
    }

    public function currentPlatformDistribution(): ?OvfnPlatformDistribution
    {
        if (! Schema::hasTable('ovfn_platform_distributions') || ! Schema::hasTable('organizations')) {
            return null;
        }

        $organization = Organization::query()->where('slug', 'fake-news')->first();

        return $organization
            ? OvfnPlatformDistribution::query()->current()->where('organization_id', $organization->id)->with('items')->first()
            : null;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function currentPlatformItems(?OvfnPlatformDistribution $distribution = null): Collection
    {
        $distribution ??= $this->currentPlatformDistribution();
        $items = $distribution?->items ?? collect();
        $total = max(0, (int) $items->sum('value'));

        return $items->map(fn ($item) => [
            'key' => $item->platform,
            'total' => (int) $item->value,
            'percentage' => $total > 0 ? round(((int) $item->value / $total) * 100, 1) : 0.0,
        ]);
    }
}
