<?php

use Livewire\Volt\Volt;
use App\Livewire\TwoFactorVerify;
use Illuminate\Support\Facades\Route;

//For testing low stock notification
use App\Models\Stock;
Route::get('/test-low-stock', function () {
    $stock = Stock::first();
    $stock->quantity = 9;
    $stock->save();
    return 'Low stock test triggered!';
});

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth','check.active', 'verified','2fa'])
//     ->name('dashboard');

Route::middleware(['auth','check.active','2fa'])->group(function () {

    //Dashboard
    Volt::route('dashboard', 'dashboard')->middleware(['auth','check.active', 'verified','2fa'])->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/2fa-config', 'settings.two-factor-authentication')->name('settings.2fa-config');

    Volt::route('settings/admin-panel', 'settings.admin-panel')
        ->middleware('role:superadmin')
        ->name('settings.admin-panel');
    Volt::route('settings/seeders', 'settings.seeders')->name('settings.seeders');

    Volt::route('users', 'users.index')->name('users');
    Volt::route('roles', 'roles.index')->name('roles');
    Volt::route('audit-trail', 'audittrail.index')->name('audittrail');

    //Brands
    Volt::route('brands', 'brands.index')->name('brands');
    Volt::route('brands/create', 'brands.create')->name('brands.create');
    Volt::route('brands/edit/{brand}', 'brands.edit')->name('brands.edit');

    //Categories
    Volt::route('categories', 'categories.index')->name('categories');
    Volt::route('categories/create', 'categories.create')->name('categories.create');
    Volt::route('categories/edit/{category}', 'categories.edit')->name('categories.edit');

    //Types
    Volt::route('types', 'types.index')->name('types');
    Volt::route('types/create', 'types.create')->name('types.create');
    Volt::route('types/edit/{type}', 'types.edit')->name('types.edit');

    //Units
    Volt::route('units', 'units.index')->name('units');
    
    //Products
    Volt::route('products', 'products.index')->name('products');
    Volt::route('products/create', 'products.create')->name('products.create');
    Volt::route('products/edit/{id}', 'products.edit')->name('products.edit');

    //Suppliers
    Volt::route('suppliers', 'suppliers.index')->name('suppliers');
    Volt::route('suppliers/create', 'suppliers.create')->name('suppliers.create');
    Volt::route('suppliers/edit/{supplier}', 'suppliers.edit')->name('suppliers.edit');
    Volt::route('suppliers/view/{id}', 'suppliers.view')->name('suppliers.view');

    //Aging
     Volt::route('agingreports', 'agingreports.index')->name('agingreports');

    //Stocks
    Volt::route('stocks', 'stocks.index')->name('stocks');
    Volt::route('stocks/create', 'stocks.create')->name('stocks.create');

    //Expiry
    Volt::route('expiryproducts', 'expiryproducts.index')->name('expiryproducts');
    // Volt::route('stocks/create', 'stocks.create')->name('stocks.create');

    //POS /Orders
    Volt::route('pos', 'pos.index')->name('pos');
    Volt::route('pos/create', 'pos.create')->name('pos.create');

    //Quotations
    Volt::route('quotations', 'quotations.index')->name('quotations');
    Volt::route('quotations/create', 'quotations.create')->name('quotations.create');
    Volt::route('quotations/edit/{quotation}', 'quotations.edit')->name('quotations.edit');

    //Agents
    Volt::route('agents', 'agents.index')->name('agents');
    Volt::route('agents/create', 'agents.create')->name('agents.create');
    Volt::route('agents/view/{id}', 'agents.view')->name('agents.view');
    Volt::route('agents/edit/{agent}', 'agents.edit')->name('agents.edit');

    //Customers
    Volt::route('customers', 'customers.index')->name('customers');
    Volt::route('customers/create', 'customers.create')->name('customers.create');
    Volt::route('customers/view/{id}', 'customers.view')->name('customers.view');
    Volt::route('customers/edit/{customer}', 'customers.edit')->name('customers.edit');

    //Locations
    Volt::route('locations', 'locations.index')->name('locations');
    Volt::route('locations/create', 'locations.create')->name('locations.create');
    Volt::route('locations/edit/{location}', 'locations.edit')->name('locations.edit');

    //Invoicing
    Volt::route('invoicing', 'invoicing.index')->name('invoicing');
    Volt::route('invoicing/create', 'invoicing.create')->name('invoicing.create');
    Volt::route('invoicing/show', 'invoicing.create')->name('invoicing.show');
    Volt::route('invoicing/{id}/edit', 'invoicing.edit')->name('invoicing.edit');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('2fa/verify', 'auth.two-factor-verify')->name('2fa.verify');
});

//run optimize clear
Route::get('optimize', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Optimize clear';
});

//run migrate
Route::get('migrate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate');
    return 'Migrate';
});

require __DIR__.'/auth.php';
