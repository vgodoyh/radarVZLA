<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OvfnVerificationTotal;
use Illuminate\Database\Seeder;

class OvfnVerificationTotalSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'fake-news')->firstOrFail();

        if (! OvfnVerificationTotal::query()
            ->where('organization_id', $organization->id)
            ->current()
            ->exists()) {
            OvfnVerificationTotal::create([
                'organization_id' => $organization->id,
                'total' => 137,
                'data_date' => '2026-07-31',
                'valid_from' => now(),
            ]);
        }
    }
}
