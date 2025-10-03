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
            'stocks.view-expiry',

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
            'quotations.pdf',

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
            'reports.view-aging-reportss',
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

            //Invoicing
            'invoicing.view',
            'invoicing.create',
            'invoicing.edit',
            'invoicing.delete',

            //Settings
            // 'settings.view',
            // 'settings.create',
            // 'settings.edit',
            // 'settings.delete',

            //Profile
            'profile.view',
            'profile.edit',


            //Locations
            'locations.view',
            'locations.create',
            'locations.edit',
            'locations.delete',

            //Special Features
            'special-features.view',
            'special-features.pdf-binding-view',

            //Settings
            'settings.view',
            'settings.2fa-config',
            'settings.seeders',

            //Invoicing
            'invoicing.view',
            'invoicing.show',
            'invoicing.create',
            'invoicing.edit',
            'invoicing.delete',

            //Purchase Requests
            'purchase-requests.view',
            'purchase-requests.show',
            'purchase-requests.create',
            'purchase-requests.edit',
            'purchase-requests.delete',

            //Purchase Orders
            'purchase-orders.view',
            'purchase-orders.show',
            'purchase-orders.create',
            'purchase-orders.edit',
            'purchase-orders.delete',
            'purchase-orders.update-status',

            //General Settings
            'general-settings.view',

            //Stock Transfer
            'stock-transfer.view',
            'stock-transfer.create',
            'stock-transfer.edit',
            'stock-transfer.delete',

            //Stock Adjustment
            'stock-adjustment.view',
            'stock-adjustment.create',
            'stock-adjustment.edit',
            'stock-adjustment.delete',

            //Database Backup
            'database-backup.view',
            'database-backup.create',
            'database-backup.delete',
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
