<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            //Dashboard
            'dashboard.view',

            //Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.disable-enable',

            //Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            //Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            //Brands
            'brands.view',
            'brands.create',
            'brands.edit',
            'brands.delete',

            //Categories
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            //Units
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        $admin = Role::updateOrCreate(['name' => 'superadmin'], ['name' => 'superadmin']);
        $admin->givePermissionTo($permissions);

        // Assign admin role to user with ID 1
        $user = \App\Models\User::find(1);
        $user->assignRole('superadmin');
    }
}
