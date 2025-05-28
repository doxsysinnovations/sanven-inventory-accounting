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

            //Audit Trail
            'audittrail.view',

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

            //Types
            'types.view',
            'types.create',
            'types.edit',
            'types.delete',

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

            //Suppliers
            'suppliers.info',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            //Stocks
            'stocks.view',
            'stocks.create',
            'stocks.edit',
            'stocks.delete',

            //Orders/POS
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',

            //Quotations
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',

            //Purchases
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',

            //Expenses
            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',

            //Reports
            'reports.view',
            'reports.create',
            'reports.edit',
            'reports.delete',

            //Customers
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'customers.info',

            //Agents
            'agents.view',
            'agents.create',
            'agents.edit',
            'agents.delete',

            //Locations
            'locations.view',
            'locations.create',
            'locations.edit',
            'locations.delete',
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
