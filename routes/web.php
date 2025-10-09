<?php

use App\Models\Stock;
use App\Models\Invoice;
use Livewire\Volt\Volt;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\DeliveryNote;

//For testing low stock notification
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\TwoFactorVerify;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth', 'check.active', '2fa'])->group(function () {

    //Dashboard
    Volt::route('dashboard', 'dashboard')->middleware(['auth', 'check.active', 'verified', '2fa'])->name('dashboard');

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
    Volt::route('users/create', 'users.create')->name('users.create');
    Volt::route('users/edit/{user}', 'users.edit')->name('users.edit');

    Volt::route('roles', 'roles.index')->name('roles');
    Volt::route('roles/create', 'roles.create')->name('roles.create');
    Volt::route('roles/edit/{role}', 'roles.edit')->name('roles.edit');

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
    Volt::route('units/create', 'units.create')->name('units.create');
    Volt::route('units/edit/{unit}', 'units.edit')->name('units.edit');

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
    Volt::route('chart-of-accounts', 'chart-of-accounts.index')->name('chartofaccounts');
    Volt::route('aging-reports', 'agingreports.index')->name('agingreports');
    Volt::route('payables', 'payables.index')->name('payables');
    Volt::route('payables/{payable}', 'payables.show')->name('payables.show');
    Volt::route('chart-of-accounts/{chartOfAccount}/view-ledger', 'chart-of-accounts.view-ledger')->name('chart-of-accounts.view-ledger');

    //Receivables
    Volt::route('receivables', 'recievables.index')->name('receivables');

    //Stocks
    Volt::route('stocks', 'stocks.index')->name('stocks');
    Volt::route('stocks/create', 'stocks.create')->name('stocks.create');
    Volt::route('stocks/{id}/edit', 'stocks.edit')->name('stocks.edit');
    Volt::route('stocks/{id}/alter', 'stocks.alter')->name('stocks.alter');
    Volt::route('stocks/returned', 'stocks.returned')->name('stocks.returned');
    Volt::route('stocks/returned', 'stocks.returned')->name('stocks.returned');
    Volt::route('stocks/broken', 'stocks.broken')->name('stocks.broken');

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
    Volt::route('quotations/view/{id}', 'quotations.view')->name('quotations.view');
    Volt::route('quotations/pdf/{quotation}', 'quotations.pdf')->name('quotations.pdf');
    Route::get('/quotations/{quotation}/stream-pdf', function (Quotation $quotation) {
        $quotation->load(['customer', 'agent', 'items.product']);

        $pdf = Pdf::loadView('livewire.quotations.pdf', [
            'quotation' => $quotation,
        ]);

        return $pdf->stream('quotation-' . $quotation->quotation_number . '.pdf');
    })->name('quotations.stream-pdf');

    //Agents
    Volt::route('agents', 'agents.index')->name('agents');
    Volt::route('agents/create', 'agents.create')->name('agents.create');
    Volt::route('agents/view/{id}', 'agents.view')->name('agents.view');
    Volt::route('agents/edit/{agent}', 'agents.edit')->name('agents.edit');
    Volt::route('agent-commissions', 'agent-commisions.index')->name('agent-commissions');

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
    Volt::route('invoicing/view/{id}', 'invoicing.view')->name('invoicing.view');
    Volt::route('invoicing/edit/{invoice}', 'invoicing.edit')->name('invoicing.edit');
    Volt::route('invoicing/pdf/{invoice}', 'invoicing.pdf')->name('invoicing.pdf');
    Route::get('/invoicing/{invoice}/stream-pdf', function (Invoice $invoice) {
        $invoice->load(['customer', 'agent', 'items',]);

        $pdf = Pdf::loadView('livewire.invoicing.pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
    })->name('invoicing.stream-pdf');

    //Special Features
    Volt::route('pdf-binding', 'special-features.pdf-binding')->name('pdf-binding');

    //Purchase Requests
    Volt::route('purchase-requests', 'purchase-requests.index')->name('purchase-requests');
    Volt::route('purchase-requests/create', 'purchase-requests.create')->name('purchase-requests.create');
    Volt::route('purchase-requests/{id}/edit', 'purchase-requests.edit')->name('purchase-requests.edit');

    //Purchase Orders
    Volt::route('purchase-orders', 'purchase-orders.index')->name('purchase-orders');
    Volt::route('purchase-orders/create', 'purchase-orders.create')->name('purchase-orders.create');
    Volt::route('purchase-orders/{id}/edit', 'purchase-orders.edit')->name('purchase-orders.edit');

    //sales orders
    Volt::route('sales-orders', 'sales-orders.index')->name('sales-orders');
    Volt::route('sales-orders/create', 'sales-orders.create')->name('sales-orders.create');
    Volt::route('sales-orders/{salesOrder}/show', 'sales-orders.show')
        ->name('sales-orders.show');

    // Volt::route('sales-orders/{id}/print', 'sales-orders.print')->name('sales-orders.print');
    Volt::route('sales-orders/{id}/edit', 'sales-orders.edit')->name('sales-orders.edit');
    Route::get('/sales-orders/{salesOrder}/stream-pdf', function (SalesOrder $salesOrder) {
        $salesOrder->load(['customer', 'agent', 'items',]);

        $pdf = Pdf::loadView('livewire.sales-orders.pdf', [
            'salesOrder' => $salesOrder,
        ]);

        return $pdf->stream('sales-orders-' . $salesOrder->order_number . '.pdf');
    })->name('sales-order.stream-pdf');

    //delivery notes
    Volt::route('delivery-notes', 'delivery-notes.index')->name('delivery-notes');
    Volt::route('delivery-notes/create', 'delivery-notes.create')->name('delivery-notes.create');
    Volt::route('delivery-notes/{deliveryNote}/show', 'delivery-notes.show')->name('delivery-notes.show');
    Volt::route('delivery-notes/{id}/print', 'delivery-notes.print')->name('delivery-notes.print');
    Volt::route('delivery-notes/{id}/edit', 'delivery-notes.edit')->name('delivery-notes.edit');
    Route::get('/delivery-notes/{deliveryNote}/stream-pdf', function (DeliveryNote $deliveryNote) {
        $deliveryNote->load(['customer', 'items', 'items.product']);

        $pdf = Pdf::loadView('livewire.delivery-notes.pdf', [
            'deliveryNote' => $deliveryNote,
        ]);

        return $pdf->stream('delivery-notes-' . $deliveryNote->delivery_note_number . '.pdf');
    })->name('delivery-notes.stream-pdf');


    Volt::route('database-backup', 'database-backup.index')->name('database-backup');
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

require __DIR__ . '/auth.php';
