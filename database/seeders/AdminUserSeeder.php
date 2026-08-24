<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'vanessa.godoy.h@gmail.com'],
            [
                'name' => 'Vanessa Godoy',
                'email_verified_at' => now(),
                'password' => Hash::make('-0607EdM@*'),
            ]
        );

        $permissions = collect([
            'manage users',
            'manage roles',
            'manage permissions',
            'manage social networks',
            'publish content',
            'view reports',
            'sync acceso justicia dashboard',
            'view jep dashboard',
            'view acceso justicia dashboard',
            'view ovfn dashboard',
            'view obu dashboard',
        ])
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->syncRoles([$role]);
    }
}
