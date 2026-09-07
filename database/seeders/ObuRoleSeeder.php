<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ObuRoleSeeder extends Seeder
{
    public function run(): void
    {
        $view = Permission::firstOrCreate(['name' => 'view obu dashboard', 'guard_name' => 'web']);
        $edit = Permission::firstOrCreate(['name' => 'edit obu metrics', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'obu', 'guard_name' => 'web'])
            ->syncPermissions([$view, $edit]);

        foreach (['admin', 'super-admin'] as $roleName) {
            if ($role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first()) {
                $role->givePermissionTo([$view, $edit]);
            }
        }
    }
}
