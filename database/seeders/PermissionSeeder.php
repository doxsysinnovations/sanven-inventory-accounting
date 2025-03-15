<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Role permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Permission permissions
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Category permissions
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Brand permissions
            'brands.view',
            'brands.create',
            'brands.edit',
            'brands.delete',

            // Unit permissions
            'units.view',
            'units.create',
            'units.edit',
            'units.delete'
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['name' => $permission]);
        }

        // Create admin role
        $adminRole = Role::updateOrCreate(['name' => 'admin'], ['name' => 'admin']);
        // Assign all permissions to admin role
        $adminRole->givePermissionTo($permissions);
    }
}
