<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessJusticeRoleSeeder extends Seeder
{
    public function run(): void
    {
        $view = Permission::firstOrCreate([
            'name' => 'view acceso justicia dashboard',
            'guard_name' => 'web',
        ]);
        $sync = Permission::firstOrCreate([
            'name' => 'sync acceso justicia dashboard',
            'guard_name' => 'web',
        ]);

        $role = Role::firstOrCreate([
            'name' => 'acceso-justicia',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions([$view, $sync]);

        foreach (['admin', 'super-admin'] as $roleName) {
            if ($generalRole = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first()) {
                $generalRole->givePermissionTo([$view, $sync]);
            }
        }
    }
}
