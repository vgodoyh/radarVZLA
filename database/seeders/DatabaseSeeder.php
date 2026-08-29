<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (config('dashboard.organizations') as $organization) {
            Organization::updateOrCreate(['slug' => $organization['slug']], $organization + ['active' => true]);
        }

        $this->call(AdminUserSeeder::class);
        $this->call(AccessJusticeRoleSeeder::class);
        $this->call(OvfnRoleSeeder::class);
        $this->call(OvfnVerificationTotalSeeder::class);
    }
}
