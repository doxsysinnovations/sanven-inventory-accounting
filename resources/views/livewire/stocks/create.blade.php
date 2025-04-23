<?php

use Livewire\Volt\Component;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;

new class extends Component {
    public string $stock_number = '';
    public int $product_id = 0;
    public string $product_name = '';
    public string $product_description = '';
    public string $brand_name = '';
    public string $product_category = '';
    public string $product_code = '';
    public string $batch_number = '';
    public int $quantity = 0;
    public float $capital_price = 0;
    public float $selling_price = 0;
    public string $expiry_date = '';
    public $suppliers = [];
    public string $search = '';
    public $products = [];
    public bool $openModal = false; // Modal visibility state
    public string $stock_location = '';
    public string $manufactured_date = '';
    public string $invoice_number = '';
    public string $batch_notes = '';
    public $locations = [];
    public $units = []; // List of units
    public string $unit = ''; // Selected unit
    public string $supplier = ''; 
    public function mount()
    {
        $this->products = Product::all(); // Load all products initially
        $this->suppliers = Supplier::all(); // Load all suppliers
        $this->units = Unit::all(); // Load all units
    }

    public function updatedSearch($value)
    {
        // Check if the input matches a product code (barcode)
        $product = Product::where('product_code', $value)->first();

        if ($product) {
            $this->selectProduct($product->id); // Automatically select the product
            $this->search = ''; // Clear the search field
            return;
        }

        // Perform a normal search for product names or partial product codes
        $this->products = Product::where('name', 'like', '%' . $value . '%')
            ->orWhere('product_code', 'like', '%' . $value . '%')
            ->get();
    }
    public function updatedCapitalPrice($value)
    {
        $this->capital_price = number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    public function updatedSellingPrice($value)
    {
        $this->selling_price = number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    public function updatedQuantity($value)
    {
        $this->quantity = (int) str_replace(',', '', $value); // Ensure the value is cast to an integer
    }
    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        $this->product_id = $product->id;
        $this->product_name = $product->name;
        $this->brand_name = $product->brand->name;
        $this->product_category = $product->category->name;
        $this->product_description = $product->description;
        $this->product_code = $product->product_code; // Reset batch number for the new batch

        $this->openModal = false; // Close the modal after selection
        flash()->success('You have selected ' . $product->name);
    }

    public function rules()
    {
        return [
            'product_name' => 'required|string|max:255',
            'batch_number' => 'required|string|max:100|unique:stocks,batch_number',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|exists:units,id', // Ensure the unit exists in the units table
            'capital_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'manufactured_date' => 'nullable|date|before_or_equal:today',
            'stock_location' => 'required|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'batch_notes' => 'nullable|string|max:1000',
            'supplier' => 'required|string|max:255',
        ];
    }

    private function generateStockNumber()
    {
        $yearPrefix = date('Y');
        $lastProduct = \App\Models\Stock::orderBy('id', 'desc')->first();
        if ($lastProduct) {
            $lastStockNumber = intval(substr($lastProduct->stock_number, -6));
            $newStockNumber = $lastStockNumber + 1;
        } else {
            $newStockNumber = 1;
        }
        return $yearPrefix . str_pad($newStockNumber, 6, '0', STR_PAD_LEFT);
    }

    public function save()
    {

        Stock::create([
            'product_name' => $this->product_name,
            'product_id' => $this->product_id,
            'batch_number' => $this->batch_number,
            'stock_number' => $this->generateStockNumber(),
            'quantity' => $this->quantity,
            'unit_id' => $this->unit, // Save the selected unit
            'capital_price' => $this->capital_price,
            'selling_price' => $this->selling_price,
            'expiration_date' => $this->expiry_date,
            'manufactured_date' => $this->manufactured_date,
            'stock_location' => $this->stock_location,
            'invoice_number' => $this->invoice_number,
            'batch_notes' => $this->batch_notes,
            'supplier_id' => $this->supplier,
        ]);

        session()->flash('message', 'Stock added successfully!');

        $this->reset(['product_name', 'batch_number', 'quantity', 'unit', 'capital_price', 'selling_price', 'expiry_date', 'manufactured_date', 'stock_location', 'invoice_number', 'batch_notes', 'supplier']);
    }
};
?>
<!-- filepath: /c:/Users/renzg/Herd/sanven-inventory-accounting/resources/views/livewire/stocks/create.blade.php -->
<div>
    <div class="mb-4">
        <nav class="flex items-center justify-between" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <a href="{{ route('stocks') }}"
                            class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 md:ml-2">Stocks</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
            <flux:button href="{{ route('stocks') }}" variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
                Back
            </flux:button>
        </nav>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 space-y-6">

        <!-- Product Search Modal -->
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md pb-6 space-y-6">
                <!-- Product & Batch Info -->
                <h2>
                    <span class="text-lg font-semibold text-gray-800 dark:text-gray-100">Product Information</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Product Code -->
                    <div>
                        <label for="product_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Code</label>
                        <flux:input id="product_code" wire:model="product_code" wire:click="$set('openModal', true)"
                            type="text" placeholder="Click to select a product"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" readonly />
                        <small class="text-gray-500 dark:text-gray-400">The product code will appear after selection.</small>
                    </div>
                
                    <!-- Product Name -->
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                        <flux:input id="product_name" wire:model="product_name" wire:click="$set('openModal', true)"
                            type="text" placeholder="Click to select a product"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" readonly />
                        <small class="text-gray-500 dark:text-gray-400">The product name will appear after selection.</small>
                    </div>
                
                    <!-- Product Description -->
                    <div class="col-span-2">
                        <label for="product_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Description</label>
                        <p id="product_description" class="text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                            {{ $product_description ?: 'No description available.' }}
                        </p>
                    </div>
                
                    <!-- Brand Name -->
                    <div>
                        <label for="brand_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand Name</label>
                        <p id="brand_name" class="text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                            {{ $brand_name ?: 'No brand specified.' }}
                        </p>
                    </div>
                
                    <!-- Product Category -->
                    <div>
                        <label for="product_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Category</label>
                        <p id="product_category" class="text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                            {{ $product_category ?: 'No category assigned.' }}
                        </p>
                    </div>
                
                    <!-- Informative Message -->
                    @if (!$product_code)
                        <div class="col-span-2 bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-300 p-4 rounded">
                            <p class="text-sm">
                                No product selected yet. Click the <strong>Search Product</strong> button to select a product.
                            </p>
                        </div>
                    @endif
                
                    <!-- Button to Open Modal -->
                    <div class="col-span-2 flex justify-end">
                        <!-- Button to Open Modal -->
                        <button type="button" wire:click="$set('openModal', true)"
                            class="btn bg-orange-700 hover:bg-orange-800 text-white px-4 py-2 rounded flex cursor-pointer relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 2a8 8 0 105.293 14.707l4.387 4.387a1 1 0 001.414-1.414l-4.387-4.387A8 8 0 0010 2zm0 2a6 6 0 100 12 6 6 0 000-12z"
                                    clip-rule="evenodd" />
                            </svg>
                            Select Product
                        </button>
                    
                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="$set('openModal', true)" class="absolute right-0 top-0 flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                </div>

                <!-- Product Search Modal -->
                @if ($openModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-25">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-3/4 max-w-4xl p-6">
                            <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Search Product</h3>

                            <!-- Search Field -->
                            <div class="mb-4 relative">
                                <input type="text" wire:model.live="search"
                                    placeholder="Search by product name, code, or scan barcode..."
                                    class="input input-bordered w-full py-2 px-4 bg-gray-200 border-2 rounded dark:bg-gray-700 dark:text-gray-100"
                                    autofocus />

                                <!-- Clear Button -->
                                @if ($search)
                                    <button type="button" wire:click="$set('search', '')"
                                        class="absolute right-2 top-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                                        ✕
                                    </button>
                                @endif
                            </div>
                            <!-- Product Table -->
                            <div class="overflow-x-auto">
                                <table
                                    class="table-auto w-full text-left border-collapse border border-gray-300 dark:border-gray-700">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Product
                                                Code</th>
                                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Name</th>
                                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Category
                                            </th>
                                            <th class="px-4 py-2 border border-gray-300 dark:border-gray-700">Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                            <tr>
                                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">
                                                    {{ $product->product_code }}</td>
                                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">
                                                    {{ $product->name }}</td>
                                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">
                                                    {{ $product->category->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">
                                                    <button type="button"
                                                        wire:click="selectProduct('{{ $product->id }}')"
                                                        class="btn btn-sm px-12 py-2 rounded-full bg-blue-500 hover:bg-blue-600 cursor-pointer text-white">
                                                        Select
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Close Button -->
                            <div class="mt-4 flex justify-end">
                                <button type="button" wire:click="$set('openModal', false)"
                                    class="btn bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <h2>
            <span class="text-lg font-semibold text-gray-800 dark:text-gray-100">Stock Information</span>
        </h2>

        <!-- Supplier -->
        <div>
            <flux:select wire:model="supplier" :label="__('Supplier')" placeholder="Select a supplier"
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600">
                @foreach ($suppliers as $supplier)
                    <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <!-- Pricing -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Capital Price -->
            <div>
                <flux:input wire:model.defer="capital_price" :label="__('Capital Price (₱)')" type="number"
                    placeholder="0.00" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                <small class="text-gray-500 dark:text-gray-400">Format: 0.00</small>
            </div>
            <!-- Selling Price -->
            <div>
                <flux:input wire:model.defer="selling_price" :label="__('Selling Price (₱)')" type="number"
                    placeholder="0.00" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                <small class="text-gray-500 dark:text-gray-400">Format: 0.00</small>
            </div>
        </div>

        <!-- Quantity, Expiry, and Manufactured Date -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Quantity -->
            <div>
                <label for="quantity"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                <flux:input id="quantity" wire:model="quantity" type="number" placeholder="Enter quantity"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                <small class="text-gray-500 dark:text-gray-400">Enter the total quantity of stock.</small>
            </div>

            <!-- Unit of Measurement -->
            <div>
                <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of
                    Measurement</label>
                <flux:select id="unit" wire:model="unit" placeholder="Select a unit"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600">
                    @foreach ($units as $unit)
                        <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <small class="text-gray-500 dark:text-gray-400">Specify the unit of measurement (e.g., Box,
                    Piece).</small>
            </div>

            <!-- Expiry Date -->
            <div>
                <label for="expiry_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry
                    Date</label>
                <flux:input id="expiry_date" wire:model="expiry_date" type="date"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                <small class="text-gray-500 dark:text-gray-400">Enter the expiry date, if applicable.</small>
            </div>

            <!-- Manufactured Date -->
            <div>
                <label for="manufactured_date"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manufactured Date</label>
                <flux:input id="manufactured_date" wire:model="manufactured_date" type="date"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                <small class="text-gray-500 dark:text-gray-400">Enter the manufacturing date, if applicable.</small>
            </div>
        </div>

        <!-- Stock Location and Invoice Number -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Stock Location -->
            <div>
                <label for="stock_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock
                    Location</label>
                <flux:select id="stock_location" wire:model="stock_location" placeholder="Select a location"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600">
                    @foreach ($locations as $location)
                        <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <small class="text-gray-500 dark:text-gray-400">Select where the stock will be stored.</small>
            </div>

            <!-- Invoice Number -->
            <div>
                <label for="invoice_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice
                    Number</label>
                <flux:input id="invoice_number" wire:model="invoice_number" type="text"
                    placeholder="Enter invoice number"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                <small class="text-gray-500 dark:text-gray-400">Enter the supplier's invoice number for
                    reference.</small>
            </div>
        </div>

        <!-- Batch Notes -->
        <div>
            <label for="batch_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Batch
                Notes</label>
            <flux:textarea id="batch_notes" wire:model="batch_notes" placeholder="Enter any notes about the batch"
                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"></flux:textarea>
            <small class="text-gray-500 dark:text-gray-400">Add any additional notes or instructions for this
                batch.</small>
        </div>
        <!-- Submit -->
        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="save" class="px-6 py-4 bg-blue-500 text-white rounded hover:bg-blue-600 cursor-pointer">
                Receive Stock
            </button>
        </div>
    </div>

</div>
