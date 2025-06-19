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
    //Types
    Volt::route('types', 'types.index')->name('types');
    //Categories
    Volt::route('categories', 'categories.index')->name('categories');
    //Units
    Volt::route('units', 'units.index')->name('units');
    //Products
    Volt::route('products', 'products.index')->name('products');
    Volt::route('products/create', 'products.create')->name('products.create');
    Volt::route('products/{productId}/edit', 'products.edit')->name('products.edit');

    //Suppliers
    Volt::route('suppliers', 'suppliers.index')->name('suppliers');

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

    //Agents
    Volt::route('agents', 'agents.index')->name('agents');

    //Customers
    Volt::route('customers', 'customers.index')->name('customers');

    //Locations
    Volt::route('locations', 'locations.index')->name('locations');



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
