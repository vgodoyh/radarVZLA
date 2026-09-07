<?php

namespace Database\Seeders;

use App\Models\ObuMetricSnapshot;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObuMetricSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'universidades')->firstOrFail();

        DB::transaction(function () use ($organization): void {
            $current = ObuMetricSnapshot::query()
                ->where('organization_id', $organization->id)
                ->current()
                ->lockForUpdate()
                ->first();

            if ($current
                && (int) $current->universities_monitored === 94
                && (int) $current->protests === 75
                && (int) $current->complaints === 68
                && $current->data_date === null) {
                return;
            }

            $changedAt = now();
            $current?->update(['valid_until' => $changedAt]);

            ObuMetricSnapshot::create([
                'organization_id' => $organization->id,
                'universities_monitored' => 94,
                'protests' => 75,
                'complaints' => 68,
                'data_date' => null,
                'valid_from' => $changedAt,
            ]);
        });
    }
}
