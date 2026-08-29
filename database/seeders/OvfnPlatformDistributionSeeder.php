<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OvfnPlatformDistribution;
use App\Models\OvfnPlatformDistributionItem;
use App\Services\OvfnEditorialMetricsService;
use Illuminate\Database\Seeder;

class OvfnPlatformDistributionSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'fake-news')->firstOrFail();

        if (OvfnPlatformDistribution::query()->where('organization_id', $organization->id)->current()->exists()) {
            return;
        }

        $distribution = OvfnPlatformDistribution::create([
            'organization_id' => $organization->id,
            'data_from_date' => '2026-06-01',
            'valid_from' => now(),
        ]);

        foreach (['tiktok' => 48, 'whatsapp' => 41, 'x' => 17, 'instagram' => 16, 'facebook' => 11] as $platform => $value) {
            OvfnPlatformDistributionItem::create([
                'distribution_id' => $distribution->id,
                'platform' => $platform,
                'value' => $value,
                'sort_order' => array_search($platform, OvfnEditorialMetricsService::PLATFORMS, true) + 1,
            ]);
        }
    }
}
