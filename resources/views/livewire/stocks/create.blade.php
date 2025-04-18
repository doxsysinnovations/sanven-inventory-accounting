<?php

use Livewire\Volt\Component;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;

new class extends Component {
    public string $product_name = '';
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

    public function mount()
    {
        $this->products = Product::all(); // Load all products initially
        $this->suppliers = Supplier::all(); // Load all suppliers
        $this->units = Unit::all(); // Load all units
    }

    public function updatedSearch()
    {
        $this->products = Product::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('code', 'like', '%' . $this->search . '%')
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
        $this->quantity = number_format((int) str_replace(',', '', $value));
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        $this->product_name = $product->name;
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

    public function save()
    {
        $this->validate();

        Stock::create([
            'product_name' => $this->product_name,
            'batch_number' => $this->batch_number,
            'quantity' => $this->quantity,
            'unit_id' => $this->unit, // Save the selected unit
            'capital_price' => $this->capital_price,
            'selling_price' => $this->selling_price,
            'expiry_date' => $this->expiry_date,
            'manufactured_date' => $this->manufactured_date,
            'stock_location' => $this->stock_location,
            'invoice_number' => $this->invoice_number,
            'batch_notes' => $this->batch_notes,
            'supplier' => $this->supplier,
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
                    <flux:input wire:model="product_code" wire:click="$set('openModal', true)"
                        :label="__('Product Code')" type="text"
                        placeholder="The product code will appear after selection"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" readonly />

                    <flux:input wire:model="product_name" wire:click="$set('openModal', true)"
                        :label="__('Product Name')" type="text" placeholder="Enter product name"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" readonly />

                    <!-- Button to Open Modal -->
                    <div class="col-span-2 flex justify-end">
                        <button type="button" wire:click="$set('openModal', true)"
                            class="btn bg-orange-700 hover:bg-orange-800 text-white px-4 py-2 rounded">
                            Search Product
                        </button>
                    </div>
                </div>

                <!-- Product Search Modal -->
                @if ($openModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-25">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-3/4 max-w-4xl p-6">
                            <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Search Product</h3>

                            <!-- Search Field -->
                            <div class="mb-4">
                                <input type="text" wire:model="search" placeholder="Search products..."
                                    class="input input-bordered w-full py-2 px-4 bg-gray-200 border-2 rounded  dark:bg-gray-700 dark:text-gray-100" />
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Quantity -->
                <flux:input wire:model="quantity" :label="__('Quantity')" type="number"
                    placeholder="Enter quantity"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            
                <!-- Unit of Measurement -->
                <flux:select wire:model="unit" :label="__('Unit of Measurement')" placeholder="Select a unit"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600">
                    @foreach ($units as $unit)
                        <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Expiry Date -->
            <flux:input wire:model="expiry_date" :label="__('Expiry Date')" type="date"
                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />

            <!-- Manufactured Date -->
            <flux:input wire:model="manufactured_date" :label="__('Manufactured Date')" type="date"
                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
        </div>

        <!-- Stock Location and Invoice Number -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Stock Location -->
            <flux:select wire:model="stock_location" :label="__('Stock Location')" placeholder="Select a location"
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600">
                @foreach ($locations as $location)
                    <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <!-- Invoice Number -->
            <flux:input wire:model="invoice_number" :label="__('Invoice Number')" type="text"
                placeholder="Enter invoice number" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
        </div>

        <!-- Batch Notes -->
        <div>
            <flux:textarea wire:model="batch_notes" :label="__('Batch Notes')"
                placeholder="Enter any notes about the batch"
                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"></flux:textarea>
        </div>
        <!-- Submit -->
        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="save" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Receive Stock
            </button>
        </div>
    </div>

</div>
