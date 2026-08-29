<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OvfnRoleSeeder extends Seeder
{
    public function run(): void
    {
        $view = Permission::firstOrCreate(['name' => 'view ovfn dashboard', 'guard_name' => 'web']);
        $edit = Permission::firstOrCreate(['name' => 'edit ovfn metrics', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'ovfn', 'guard_name' => 'web'])
            ->syncPermissions([$view]);

        foreach (['admin', 'super-admin'] as $roleName) {
            if ($role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first()) {
                $role->givePermissionTo([$view, $edit]);
            }
        }
    }
}
