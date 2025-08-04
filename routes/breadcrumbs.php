<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

Breadcrumbs::for('home', fn (Trail $trail) =>
    $trail->push('Home', route('home'))
);

Breadcrumbs::for('dashboard', fn (Trail $trail) =>
    $trail->push('Dashboard', route('dashboard'))
);

Breadcrumbs::for('invoicing', fn (Trail $trail) =>
    $trail->push('Invoicing', route('invoicing'))
);

Breadcrumbs::for('invoicing.create', fn (Trail $trail) =>
     $trail->parent('invoicing')->push('Create Invoice', route('invoicing.create'))
);

Breadcrumbs::for('invoicing.edit', fn (Trail $trail, $invoice) =>
     $trail->parent('invoicing')->push("Edit Invoice #{$invoice->id}", route('invoicing.edit', $invoice))
);

Breadcrumbs::for('invoicing.view', fn (Trail $trail, $invoice) =>
     $trail->parent('invoicing')->push("View Invoice #{$invoice->id}", route('invoicing.view', $invoice))
);

Breadcrumbs::for('agents', fn (Trail $trail) =>
    $trail->push('Agents', route('agents'))
);

Breadcrumbs::for('agents.create', fn (Trail $trail) =>
     $trail->parent('agents')->push('Add Agent', route('agents.create'))
);

Breadcrumbs::for('agents.edit', fn (Trail $trail, $agent) =>
    $trail->parent('agents')->push("Edit Agent: {$agent->name}", route('agents.edit', $agent))
);

Breadcrumbs::for('agents.view', fn (Trail $trail, $agent) =>
    $trail->parent('agents')->push("Agent Profile: {$agent->name}", route('agents.view', $agent))
);

Breadcrumbs::for('customers', fn (Trail $trail) =>
    $trail->push('Customers', route('customers'))
);

Breadcrumbs::for('customers.create', fn (Trail $trail) =>
     $trail->parent('customers')->push('Add Customer', route('customers.create'))
);

Breadcrumbs::for('customers.edit', fn (Trail $trail, $customer) =>
    $trail->parent('customers')->push("Edit Customer: {$customer->name}", route('customers.edit', $customer))
);

Breadcrumbs::for('customers.view', fn (Trail $trail, $customer) =>
    $trail->parent('customers')->push("Customer Profile: {$customer->name}", route('customers.view', $customer))
);

Breadcrumbs::for('quotations', fn (Trail $trail) =>
    $trail->push('Quotations', route('quotations'))
);

Breadcrumbs::for('quotations.create', fn (Trail $trail) =>
     $trail->parent('quotations')->push('Create Quotation', route('quotations.create'))
);

Breadcrumbs::for('quotations.edit', fn (Trail $trail, $quotation) =>
    $trail->parent('quotations')->push("Edit Quoation #{$quotation->id}", route('quotations.edit', $quotation))
);

Breadcrumbs::for('quotations.view', fn (Trail $trail, $quotation) =>
    $trail->parent('quotations')->push("View Quotation #{$quotation->id}", route('quotations.view', $quotation))
);

Breadcrumbs::for('stocks', fn (Trail $trail) =>
    $trail->push('Stocks', route('stocks'))
);

Breadcrumbs::for('stocks.create', fn (Trail $trail) =>
     $trail->parent('stocks')->push('Create Stock', route('stocks.create'))
);

Breadcrumbs::for('expiryproducts', fn (Trail $trail) =>
    $trail->push('Expiry Products', route('expiryproducts'))
);

Breadcrumbs::for('products', fn (Trail $trail) =>
    $trail->push('Products', route('products'))
);

Breadcrumbs::for('products.create', fn (Trail $trail) =>
    $trail->parent('products')->push('Add Product', route('products.create'))
);

Breadcrumbs::for('products.edit', fn (Trail $trail, $product) => 
    $trail->parent('products')->push("Edit Product: {$product}", route('products.edit', $product)
    )
);

Breadcrumbs::for('brands', fn (Trail $trail) =>
    $trail->push('Brands', route('brands'))
);

Breadcrumbs::for('brands.create', fn (Trail $trail) =>
    $trail->parent('brands')->push('Add Brand', route('brands.create'))
);

Breadcrumbs::for('brands.edit', fn (Trail $trail, $brand) =>
    $trail->parent('brands')->push("Edit Brand: {$brand->name}", route('brands.edit', $brand))
);

Breadcrumbs::for('categories', fn (Trail $trail) =>
    $trail->push('Categories', route('categories'))
);

Breadcrumbs::for('categories.create', fn (Trail $trail) =>
    $trail->parent('categories')->push('Add Category', route('categories.create'))
);

Breadcrumbs::for('categories.edit', fn (Trail $trail, $category) =>
    $trail->parent('categories')->push("Edit Category: {$category->name}", route('categories.edit', $category))
);

Breadcrumbs::for('types', fn (Trail $trail) =>
    $trail->push('Types', route('types'))
);

Breadcrumbs::for('types.create', fn (Trail $trail) =>
    $trail->parent('types')->push('Add Type', route('types.create'))
);

Breadcrumbs::for('types.edit', fn (Trail $trail, $type) =>
    $trail->parent('types')->push("Edit Type: {$type->name}", route('types.edit', $type))
);

Breadcrumbs::for('units', fn (Trail $trail) =>
    $trail->push('Units', route('units'))
);

Breadcrumbs::for('units.create', fn (Trail $trail) =>
    $trail->parent('units')->push('Add Unit', route('units.create'))
);

Breadcrumbs::for('units.edit', fn (Trail $trail, $unit) =>
    $trail->parent('units')->push("Edit Unit: {$unit->name}", route('units.edit', $unit))
);

Breadcrumbs::for('units.view', fn (Trail $trail, $unit) =>
    $trail->parent('units')->push("Unit Profile: {$unit->name}", route('units.view', $unit))
);

Breadcrumbs::for('suppliers', fn (Trail $trail) =>
    $trail->push('Suppliers', route('suppliers'))
);

Breadcrumbs::for('suppliers.create', fn (Trail $trail) =>
    $trail->parent('suppliers')->push('Add Supplier', route('suppliers.create'))
);

Breadcrumbs::for('suppliers.edit', fn (Trail $trail, $supplier) =>
    $trail->parent('suppliers')->push("Edit Supplier: {$supplier->name}", route('suppliers.edit', $supplier))
);

Breadcrumbs::for('suppliers.view', fn (Trail $trail, $supplier) =>
    $trail->parent('suppliers')->push("Supplier Profile: {$supplier->name}", route('suppliers.view', $supplier))
);

Breadcrumbs::for('users', fn (Trail $trail) =>
    $trail->push('Users', route('users'))
);

Breadcrumbs::for('users.create', fn (Trail $trail) =>
    $trail->parent('users')->push('Add User', route('users.create'))
);

Breadcrumbs::for('users.edit', fn (Trail $trail, $user) =>
    $trail->parent('users')->push("Edit User: {$user->name}", route('users.edit', $user))
);

Breadcrumbs::for('roles', fn (Trail $trail) =>
    $trail->push('Roles', route('roles'))
);

Breadcrumbs::for('roles.create', fn (Trail $trail) =>
    $trail->parent('roles')->push('Add Role', route('roles.create'))
);

Breadcrumbs::for('roles.edit', fn (Trail $trail, $role) =>
    $trail->parent('roles')->push("Edit Role: {$role->name}", route('roles.edit', $role))
);

Breadcrumbs::for('audittrail', fn (Trail $trail) =>
    $trail->push('Audit Trail', route('audittrail'))
);

Breadcrumbs::for('locations', fn (Trail $trail) =>
    $trail->push('Locations', route('locations'))
);

Breadcrumbs::for('locations.create', fn (Trail $trail) =>
    $trail->parent('locations')->push('Add Location', route('locations.create'))
);

Breadcrumbs::for('locations.edit', fn (Trail $trail, $location) =>
    $trail->parent('locations')->push("Edit Location: {$location->name}", route('locations.edit', $location))
);