<?php

namespace Database\Seeders;

use App\Models\ObuMonitoringPeriod;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObuMonitoringPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'universidades')->firstOrFail();

        DB::transaction(function () use ($organization): void {
            $current = ObuMonitoringPeriod::query()->where('organization_id', $organization->id)->current()->lockForUpdate()->first();
            if ($current
                && $current->period_start?->toDateString() === '2026-01-01'
                && $current->period_end?->toDateString() === '2026-06-30'
                && (int) $current->analyzed_information === 934
                && (int) $current->protests === 75
                && (int) $current->complaints === 68) {
                return;
            }

            $changedAt = now();
            $current?->update(['valid_until' => $changedAt]);
            ObuMonitoringPeriod::create([
                'organization_id' => $organization->id,
                'period_start' => '2026-01-01',
                'period_end' => '2026-06-30',
                'analyzed_information' => 934,
                'protests' => 75,
                'complaints' => 68,
                'data_date' => '2026-06-30',
                'valid_from' => $changedAt,
            ]);
        });
    }
}
