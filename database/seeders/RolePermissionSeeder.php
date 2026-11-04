<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Create the roles and permissions. Safe to run more than once.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $requester = [
            'create requisitions',
            'register deposits',
            'create expense reports',
        ];

        $coordinator = [
            ...$requester,
            'approve team requisitions',
            'approve team expense reports',
        ];

        $manager = [
            ...$requester,
            'approve requisitions',
            'review expense reports',
            'close settlements',
            'view all requisitions',
        ];

        $all = array_values(array_unique([...$coordinator, ...$manager, 'manage users', 'manage catalogs']));

        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'requester', 'guard_name' => 'web'])->syncPermissions($requester);
        Role::firstOrCreate(['name' => 'coordinator', 'guard_name' => 'web'])->syncPermissions($coordinator);
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web'])->syncPermissions($manager);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions($all);
    }
}
