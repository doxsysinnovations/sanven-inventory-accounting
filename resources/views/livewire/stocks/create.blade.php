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
    public int $unit_id = 0; // Selected unit
    public string $supplier = '';
    public int $currentStep = 1;
    public function mount()
    {
        $this->products = Product::all(); // Load all products initially
        $this->suppliers = Supplier::all(); // Load all suppliers
        $this->units = Unit::all(); // Load all units
    }

    public function nextStep()
    {
        $this->validateStep();
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    private function validateStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'product_name' => 'required|string|max:255',
                'product_code' => 'required|string|max:255',
            ]);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'quantity' => 'required|integer|min:1',
                'unit_id' => 'required|integer|exists:units,id', // Ensure the unit exists in the units table
                'capital_price' => 'required|numeric|min:1',
                'selling_price' => 'required|numeric|min:1',
                'expiry_date' => 'required|date|after:today',
                'manufactured_date' => 'required|date|before_or_equal:today',
                'invoice_number' => 'nullable|string|max:100',
                'supplier' => 'required|string|max:255',
            ]);
        }
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
            // 'batch_number' => 'required|string|max:100|unique:stocks,batch_number',
            'quantity' => 'required|integer|min:1',
            'unit_id' => 'required|integer|exists:units,id', // Ensure the unit exists in the units table
            'capital_price' => 'required|numeric|min:1',
            'selling_price' => 'required|numeric|min:1',
            'expiry_date' => 'required|date|after:today',
            'manufactured_date' => 'required|date|before_or_equal:today',
            'stock_location' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'batch_notes' => 'nullable|string|max:1000',
            'supplier' => 'required|string|max:255',
        ];
    }

    private function generateStockNumber()
{
    $yearPrefix = date('Y');

    // Use a database transaction to ensure uniqueness
    return \DB::transaction(function () use ($yearPrefix) {
        // Lock the table to prevent race conditions
        $lastStock = \App\Models\Stock::where('stock_number', 'like', $yearPrefix . '%')
            ->lockForUpdate() // Lock the rows for update
            ->orderByRaw('CAST(SUBSTRING(stock_number, 5) AS UNSIGNED) DESC') // Order by the numeric part of the stock number
            ->first();

        if ($lastStock) {
            $lastStockNumber = intval(substr($lastStock->stock_number, 4)); // Extract the numeric part after the year prefix
            $newStockNumber = $lastStockNumber + 1;
        } else {
            $newStockNumber = 1; // Start from 1 if no stock exists for the current year
        }

        return $yearPrefix . str_pad($newStockNumber, 6, '0', STR_PAD_LEFT);
    });
}
    public function save()
    {

        $this->validate();
        // Check if the product is expired
        Stock::create([
            'product_name' => $this->product_name,
            'product_id' => $this->product_id,
            'batch_number' => $this->batch_number,
            'quantity' => $this->quantity,
            'unit_id' => $this->unit_id, // Save the selected unit
            'capital_price' => $this->capital_price,
            'selling_price' => $this->selling_price,
            'expiration_date' => $this->expiry_date,
            'manufactured_date' => $this->manufactured_date,
            'stock_location' => $this->stock_location,
            'invoice_number' => $this->invoice_number,
            'batch_notes' => $this->batch_notes,
            'supplier_id' => $this->supplier,
        ]);
        
        flash()->success('Stock added successfully! ');
        $this->currentStep = 1; // Reset to the first step

        $this->reset(['product_name','product_code','brand_name','product_category', 'batch_number','product_description', 'quantity', 'unit_id', 'capital_price', 'selling_price', 'expiry_date', 'manufactured_date', 'stock_location', 'invoice_number', 'batch_notes', 'supplier']);
    }
};
?>
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

  <!-- Stepper Navigation -->
  <div class="flex items-center justify-between mb-6">
    <div class="flex space-x-4">
        <!-- Step 1 -->
        <button wire:click="$set('currentStep', 1)" 
            class="px-6 py-3 rounded-full font-medium transition-all duration-300 shadow-md
            {{ $currentStep === 1 ? 'bg-blue-600 text-white dark:bg-blue-500' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            Step 1: Product Info
        </button>

        <!-- Step 2 -->
        <button wire:click="$set('currentStep', 2)" 
            class="px-6 py-3 rounded-full font-medium transition-all duration-300 shadow-md
            {{ $currentStep === 2 ? 'bg-blue-600 text-white dark:bg-blue-500' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            Step 2: Stock Info
        </button>

        <!-- Step 3 -->
        <button wire:click="$set('currentStep', 3)" 
            class="px-6 py-3 rounded-full font-medium transition-all duration-300 shadow-md
            {{ $currentStep === 3 ? 'bg-blue-600 text-white dark:bg-blue-500' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            Step 3: Additional Details
        </button>
    </div>
</div>

    @if ($currentStep === 1)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Product Information</h2>
            <!-- Informative Message -->
            @if (!$product_code)
                <div
                    class="col-span-1 flex items-center justify-between sm:col-span-2 lg:col-span-3 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 p-4 rounded">
                    <p class="text-sm">
                        No product selected yet. Click the <strong>Search Product</strong> button to select a product.
                    </p>
                    <div class="flex justify-end mt-4">
                        <button type="button" wire:click="$set('openModal', true)"
                            class="px-6 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            Select Product
                        </button>
                    </div>
                </div>
            @else
                <div
                    class="col-span-1 flex items-center justify-between  sm:col-span-2 lg:col-span-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 p-4 rounded">
                    <p class="text-sm">
                        You have selected a product. Click the <strong>Select Product</strong> button again to change
                        the product.
                    </p>
                    <div class="flex justify-end mt-4">
                        <button type="button" wire:click="$set('openModal', true)"
                            class="px-6 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                            Select Product
                        </button>
                    </div>
                </div>
            @endif
            <!-- Product Code -->
            <div class="mt-6">
                <label for="product_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product
                    Code</label>
                <flux:input id="product_code" wire:model="product_code" wire:click="$set('openModal', true)"
                    type="text" placeholder="Click to select a product"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" readonly />
            </div>
            <!-- Product Name -->
            <div class="mt-3">
                <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product
                    Name</label>
                <flux:input id="product_name" wire:model="product_name" wire:click="$set('openModal', true)"
                    type="text" placeholder="Click to select a product"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" readonly />
            </div>

            <!-- Product Description -->
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 mt-3">
                <label for="product_description"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product
                    Description</label>
                <p id="product_description"
                    class="text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                    {{ $product_description ?: 'No description available.' }}
                </p>
            </div>

            <!-- Brand Name -->
            <div class="mt-3">
                <label for="brand_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand
                    Name</label>
                <p id="brand_name" class="text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                    {{ $brand_name ?: 'No brand specified.' }}
                </p>
            </div>

            <!-- Product Category -->
            <div class="mt-3">
                <label for="product_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product
                    Category</label>
                <p id="product_category"
                    class="text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                    {{ $product_category ?: 'No category assigned.' }}
                </p>
            </div>


            <!-- Search Product Button -->


            <!-- Next Button -->
            <div class="flex justify-end mt-4">
                <button wire:click="nextStep" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Next
                </button>
            </div>
        </div>

        <div>

            <!-- Product Search Modal -->
            @if ($openModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-25">
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg w-3/4 max-w-4xl p-6">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Search Product</h3>

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
    @endif


    @if ($currentStep === 2)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Stock Information</h2>


            <!-- Supplier -->
            <div class="mt-3">
                <label for="supplier"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                <flux:select id="supplier" wire:model="supplier" placeholder="Select a supplier"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
        @error('supplier') border-red-500 dark:border-red-500 @enderror">
                    @foreach ($suppliers as $supplier)
                        <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('supplier')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pricing -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <!-- Capital Price -->
                <div>
                    <label for="capital_price"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capital
                        Price (₱)</label>
                    <flux:input id="capital_price" wire:model="capital_price" type="number" placeholder="0.00"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('capital_price') border-red-500 dark:border-red-500 @enderror" />
                    <small class="text-gray-500 dark:text-gray-400">Format: 0.00</small>
                    @error('capital_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Selling Price -->
                <div>
                    <label for="selling_price"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling
                        Price (₱)</label>
                    <flux:input id="selling_price" wire:model="selling_price" type="number" placeholder="0.00"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('selling_price') border-red-500 dark:border-red-500 @enderror" />
                    <small class="text-gray-500 dark:text-gray-400">Format: 0.00</small>
                    @error('selling_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Quantity, Expiry, and Manufactured Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Quantity -->
                <div>
                    <label for="quantity"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                    <flux:input id="quantity" wire:model="quantity" type="number" placeholder="Enter quantity"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('quantity') border-red-500 dark:border-red-500 @enderror" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the total quantity of stock.</small>
                    @error('quantity')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit of Measurement -->
                <div>
                    <label for="unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of
                        Measurement</label>
                    <flux:select id="unit_id" wire:model="unit_id" placeholder="Select a unit"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('unit_id') border-red-500 dark:border-red-500 @enderror">
                        @foreach ($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <small class="text-gray-500 dark:text-gray-400">Specify the unit of measurement (e.g., Box,
                        Piece).</small>
                    @error('unit_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Manufactured Date -->
                <div>
                    <label for="manufactured_date"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manufactured Date</label>
                    <flux:input id="manufactured_date" wire:model="manufactured_date" type="date"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('manufactured_date') border-red-500 dark:border-red-500 @enderror" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the manufacturing date, if
                        applicable.</small>
                    @error('manufactured_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiry Date -->
                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry
                        Date</label>
                    <flux:input id="expiry_date" wire:model="expiry_date" type="date"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('expiry_date') border-red-500 dark:border-red-500 @enderror" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the expiry date, if applicable.</small>
                    @error('expiry_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Invoice Number -->
                <div class="mt-3">
                    <label for="invoice_number"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice
                        Number</label>
                    <flux:input id="invoice_number" wire:model="invoice_number" type="text"
                        placeholder="Enter invoice number"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
        @error('invoice_number') border-red-500 dark:border-red-500 @enderror" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the supplier's invoice number for
                        reference.</small>
                    @error('invoice_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Navigation Buttons -->

            </div>

            <div class="flex justify-between mt-4">
                <button wire:click="previousStep" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Back
                </button>
                <button wire:click="nextStep" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Next
                </button>
            </div>
    @endif

    @if ($currentStep === 3)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Details</h2>
            <!-- Stock Location -->
            <div>
                <label for="stock_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock
                    Location</label>
                <flux:select id="stock_location" wire:model="stock_location" placeholder="Select a location"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded">
                    @foreach ($locations as $location)
                        <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <small class="text-gray-500 dark:text-gray-400">Select where the stock will be stored.</small>
            </div>

            <!-- Batch Notes -->
            <div class="mt-3">
                <label for="batch_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Batch
                    Notes</label>
                <flux:textarea id="batch_notes" wire:model="batch_notes"
                    placeholder="Enter any notes about the batch"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
    @error('batch_notes') border-red-500 dark:border-red-500 @enderror">
                </flux:textarea>
                <small class="text-gray-500 dark:text-gray-400">Add any additional notes or instructions for this
                    batch.</small>
                @error('batch_notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- Submit Button -->
            <div class="flex justify-between mt-4">
                <button wire:click="previousStep" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Back
                </button>
                <button wire:click="save" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Submit
                </button>
            </div>
        </div>
    @endif
</div>

</div>
