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
                && (int) $current->complaints_five_years === 1226
                && $current->rights_breakdown === [
                    'fair_wages' => 68,
                    'infrastructure_damage' => 19,
                    'student_welfare' => 12,
                    'university_autonomy' => 8,
                    'freedom_of_expression' => 6,
                    'public_affairs_participation' => 14,
                    'strike' => 29,
                    'gathering' => 20,
                    'banner_protest' => 7,
                    'march' => 14,
                    'other' => 5,
                ]
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
                'complaints_five_years' => 1226,
                'rights_breakdown' => [
                    'fair_wages' => 68,
                    'infrastructure_damage' => 19,
                    'student_welfare' => 12,
                    'university_autonomy' => 8,
                    'freedom_of_expression' => 6,
                    'public_affairs_participation' => 14,
                    'strike' => 29,
                    'gathering' => 20,
                    'banner_protest' => 7,
                    'march' => 14,
                    'other' => 5,
                ],
                'data_date' => null,
                'valid_from' => $changedAt,
            ]);
        });
    }
}
