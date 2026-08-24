<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessJusticeRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'view acceso justicia dashboard',
            'guard_name' => 'web',
        ]);

        $role = Role::firstOrCreate([
            'name' => 'acceso-justicia',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions([$permission]);
    }
}
