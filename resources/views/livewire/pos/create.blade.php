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
    public $customers = [];
    public bool $showAddCustomerModal = false;
    public string $new_customer_name = '';
    public string $new_customer_email = '';
    public string $new_customer_phone = '';
    public string $new_customer_address = '';
    public ?int $customer_id = null; // Set to null by default
    public $selectedCustomer = null;
    public array $cart = [];
    public bool $showStockModal = false;
    public array $quantities = [];
    public array $selectedProducts = [];
    public float $tax = 0;
    public float $discount = 0;
    public string $payment_terms = 'Net 30';
    public int $assigned_agent_id = 0;
    public $agents = []; // Load agents in the `mount` method
    public string $invoice_status = 'Pending';
    public string $invoice_due_date = '';
    public string $invoice_notes = '';

    public function mount()
    {
        $this->customers = \App\Models\Customer::all(); // Load all customers
        $this->products = Stock::all(); // Load all products initially
        $this->suppliers = Supplier::all(); // Load all suppliers
        $this->units = Unit::all(); // Load all units
        $this->agents = \App\Models\Agent::all(); // Load all agents
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
    public function updateSelectedCustomer()
    {
        $this->selectedCustomer = $this->customers->firstWhere('id', $this->customer_id);
    }
    public function toggleSelectAll()
    {
        if (count($this->selectedProducts) === count($this->products)) {
            $this->selectedProducts = [];
        } else {
            $this->selectedProducts = $this->products->pluck('id')->toArray();
        }
    }
    public function removeProductFromCart($index)
    {
        $productId = $this->cart[$index]['id'];
        $quantityToRestore = $this->cart[$index]['quantity'];

        // Restore the stock quantity in the products array
        $productIndex = collect($this->products)->search(fn($item) => $item->id === $productId);
        if ($productIndex !== false) {
            $this->products[$productIndex]->quantity += $quantityToRestore;
        }

        // Remove the product from the cart
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Reindex the array
    }
    public function updateCartQuantity($index)
    {
        // Perform any additional logic when the quantity changes
        $quantity = $this->cart[$index]['quantity'];

        // Ensure the quantity does not exceed the available stock
        if ($quantity > $this->cart[$index]['available_quantity']) {
            $this->cart[$index]['quantity'] = $this->cart[$index]['available_quantity'];
            flash()->error('Quantity exceeds available stock.');
        }

        // Optionally, update other properties or perform calculations
        $this->calculateGrandTotal();
    }
    public function calculateGrandTotal()
    {
        $this->grandTotal = collect($this->cart)->sum(fn($item) => $item['selling_price'] * $item['quantity']);
    }
    public function getGrandTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['selling_price'] * $item['quantity']);
    }
    public function getCanAddToCartProperty()
    {
        foreach ($this->selectedProducts as $productId) {
            $quantity = $this->quantities[$productId] ?? 0;
            if ($quantity > 0) {
                return true; // At least one product has a valid quantity
            }
        }
        return false; // No valid quantities
    }
    public function getCartQuantity($index)
    {
        return $this->cart[$index]['quantity'] ?? 0; // Return 0 if the quantity is not set
    }
    public function addSelectedToCart()
    {
        foreach ($this->selectedProducts as $productId) {
            $stock = Stock::find($productId);

            if ($stock) {
                $quantityToAdd = $this->quantities[$productId] ?? 1; // Default to 1 if no quantity is specified

                if ($quantityToAdd > $stock->quantity) {
                    flash()->error("Cannot add more than available stock for {$stock->product_name}.");
                    continue;
                }

                // Check if the stock number already exists in the cart
                $existingIndex = collect($this->cart)->search(fn($item) => $item['stock_number'] === $stock->stock_number);

                if ($existingIndex === false) {
                    // Add the stock to the cart if it doesn't exist
                    $this->cart[] = [
                        'id' => $stock->id,
                        'name' => $stock->product_name,
                        'quantity' => $quantityToAdd,
                        'available_quantity' => $stock->quantity,
                        'selling_price' => $stock->selling_price,
                        'stock_number' => $stock->stock_number,
                        'expiration_date' => $stock->formatted_expiration_date,
                    ];
                } else {
                    // Update the quantity if the stock already exists in the cart
                    $newQuantity = $this->cart[$existingIndex]['quantity'] + $quantityToAdd;

                    if ($newQuantity <= $stock->quantity) {
                        $this->cart[$existingIndex]['quantity'] = $newQuantity;
                    } else {
                        flash()->error("Cannot add more than available stock for {$stock->product_name}.");
                    }
                }

                // Temporarily deduct the stock from the products array
                $productIndex = collect($this->products)->search(fn($item) => $item->id === $productId);
                if ($productIndex !== false) {
                    $this->products[$productIndex]['quantity'] -= $quantityToAdd;
                }
            }
        }

        // Clear selection and close modal
        $this->selectedProducts = [];
        $this->quantities = [];
        $this->showStockModal = false;

        // Flash success message
        flash()->success('Selected products added to cart successfully!');
    }
    public function addCustomer()
    {
        $this->validate([
            'new_customer_name' => 'required|string|max:255',
            'new_customer_email' => 'nullable|email|max:255|unique:customers,email',
            'new_customer_address' => 'nullable|string|max:500',
        ]);

        // Create the new customer
        $customer = \App\Models\Customer::create([
            'name' => $this->new_customer_name,
            'email' => $this->new_customer_email,
            'phone' => $this->new_customer_phone ?? null, // Ensure phone is nullable
            'address' => $this->new_customer_address ?? null, // Ensure address is nullable
        ]);

        // Add the new customer to the dropdown
        $this->customers = \App\Models\Customer::all();

        // Set the newly created customer as selected
        $this->customer_id = $customer->id;

        // Reset modal fields and close the modal
        $this->reset(['new_customer_name', 'new_customer_email', 'new_customer_phone', 'showAddCustomerModal']);

        // Flash success message
        flash()->success('Customer added successfully!');
    }

    private function validateStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'customer_id' => 'required|int|max:255',
            ]);
        } elseif ($this->currentStep === 2) {
            // Prevent moving to the next step if no products are selected
            if (empty($this->cart)) {
                flash()->error('You must select at least one product to proceed.');

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'selectedProducts' => 'You must select at least one product to proceed.',
                ]);
            }
        }
    }
    public function loadStocks()
    {
        $this->products = Stock::orderByRaw(
            "
            CASE
                WHEN expiration_date < CURDATE() THEN 1
                WHEN expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 2
                ELSE 3
            END, expiration_date ASC
        ",
        )->paginate(10); // Paginate with 10 items per page
    }

    public function updatedSearch($value)
    {
        $this->products = Stock::where('product_name', 'like', '%' . $value . '%')
            ->orWhere('stock_number', 'like', '%' . $value . '%')
            ->orderByRaw(
                "
                CASE
                    WHEN expiration_date < CURDATE() THEN 1
                    WHEN expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 2
                    ELSE 3
                END, expiration_date ASC
            ",
            )
            ->paginate(10);
    }
    public function updatedCapitalPrice($value)
    {
        $this->capital_price = number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    public function updatedSellingPrice($value)
    {
        $this->selling_price = number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    public function updatedQuantities($value, $key)
    {
        if ((int) $value > 0) {
            if (!in_array($key, $this->selectedProducts)) {
                $this->selectedProducts[] = $key;
            }
        } else {
            $this->selectedProducts = array_filter($this->selectedProducts, fn($id) => $id != $key);
        }
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
        $lastProduct = \App\Models\Stock::orderBy('id', 'desc')->first();
        if ($lastProduct) {
            $lastStockNumber = intval(substr($lastProduct->stock_number, -6));
            $newStockNumber = $lastStockNumber + 1;
        } else {
            $newStockNumber = 1;
        }
        return $yearPrefix . str_pad($newStockNumber, 6, '0', STR_PAD_LEFT);
    }

    public function updatedCart($value, $key)
    {
        [$index, $field] = explode('.', $key);

        if ($field === 'quantity') {
            // Debug the quantity
        }
    }
    public function save()
    {
        $this->validate();
        // Check if the product is expired
        Stock::create([
            'product_name' => $this->product_name,
            'product_id' => $this->product_id,
            'batch_number' => $this->batch_number,
            'stock_number' => $this->generateStockNumber(),
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

        $this->reset(['product_name', 'product_code', 'brand_name', 'product_category', 'batch_number', 'product_description', 'quantity', 'unit_id', 'capital_price', 'selling_price', 'expiry_date', 'manufactured_date', 'stock_location', 'invoice_number', 'batch_notes', 'supplier']);
    }

    public function addProductToCart($stockId)
    {
        $stock = Stock::find($stockId);

        if ($stock) {
            // Check if the stock is already in the cart
            $existingIndex = collect($this->cart)->search(fn($item) => $item['id'] === $stockId);

            if ($existingIndex === false) {
                // Add the stock to the cart
                $this->cart[] = [
                    'id' => $stock->id,
                    'name' => $stock->product_name,
                    'quantity' => 1, // Default quantity
                    'available_quantity' => $stock->quantity, // Track available stock
                    'stock_number' => $stock->stock_number, // Track available stock
                    'expiration_date' => $stock->expiration_date, // Track available stock
                ];
            } else {
                // Increment the quantity if the stock is already in the cart
                if ($this->cart[$existingIndex]['quantity'] < $stock->quantity) {
                    $this->cart[$existingIndex]['quantity']++;
                    flash()->success('New Stocks added to cart.');
                } else {
                    flash()->error('Cannot add more than available stock.');
                }
            }
        } else {
            flash()->error('Stock not found.');
        }
    }
};
?>
<div>
    <div class="mb-4">
        <nav class="flex items-center justify-between" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
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
                        <a href="{{ route('pos') }}"
                            class="ml-1 text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 md:ml-2">Invoices</a>
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
            <flux:button href="{{ route('pos') }}" variant="primary">
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
    <h1>
        <span class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Create Invoice</span>
        <span class="text-sm text-gray-500 ml-2 dark:text-gray-400">Step {{ $currentStep }} of 3</span>
    </h1>
    <div class="flex items-center justify-between mt-6 mb-6">

        <div class="flex space-x-4">
            <!-- Step 1 -->
            <button wire:click="$set('currentStep', 1)"
                class="px-6 py-3 rounded-full font-medium transition-all duration-300 shadow-md
            {{ $currentStep === 1 ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                Step 1: Customer Info
            </button>

            <!-- Step 2 -->
            <button wire:click="$set('currentStep', 2)"
                class="px-6 py-3 rounded-full font-medium transition-all duration-300 shadow-md
            {{ $currentStep === 2 ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                Step 2: Stock Info
            </button>

            <!-- Step 3 -->
            <button wire:click="$set('currentStep', 3)"
                class="px-6 py-3 rounded-full font-medium transition-all duration-300 shadow-md
            {{ $currentStep === 3 ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                Step 3: Additional Details
            </button>
        </div>
    </div>

    @if ($currentStep === 1)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h2>

            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Step 1: Select or Add a Customer
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Please select an existing customer from the list or add a new customer if they are not listed.
                </p>

                <!-- Customer Selection -->
                <div class="flex flex-col gap-4">
                    <div>
                        <label for="customer_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-grow">
                                <flux:select id="customer_id" wire:model="customer_id"
                                    wire:change="updateSelectedCustomer" placeholder="Select a customer"
                                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full h-full rounded
                            @error('customer_id') border-red-500 dark:border-red-500 @enderror">
                                    <flux:select.option value="0" disabled>Select a customer</flux:select.option>
                                    @foreach ($customers as $customer)
                                        <flux:select.option value="{{ $customer->id }}">
                                            {{ $customer->name }} - {{ $customer->email ?? 'No Email' }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <button wire:click="$set('showAddCustomerModal', true)" type="button"
                                class="px-4 uppercase text-sm bg-orange-500 py-1 font-semibold text-white rounded hover:bg-orange-600 cursor-pointer h-full">
                                + Add New Customer
                            </button>
                        </div>
                        <small class="text-gray-500 dark:text-gray-400">Select the customer for this invoice or add a
                            new one.</small>
                        @error('customer_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Customer Details Preview -->
                    @if ($selectedCustomer)
                        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Selected Customer Details:
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Name:</strong> {{ $selectedCustomer['name'] }}<br>
                                <strong>Email:</strong> {{ $selectedCustomer['email'] ?? 'No Email' }}<br>
                                <strong>Phone:</strong> {{ $selectedCustomer['phone'] ?? 'No Phone' }}<br>
                                <strong>Address:</strong> {{ $selectedCustomer['address'] ?? 'No Address' }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Add Customer Modal -->
                @if ($showAddCustomerModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add New Customer
                            </h2>

                            <!-- Customer Name -->
                            <div class="mb-4">
                                <label for="new_customer_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <flux:input id="new_customer_name" wire:model="new_customer_name" type="text"
                                    placeholder="Enter customer name"
                                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                            @error('new_customer_name') border-red-500 dark:border-red-500 @enderror" />
                                @error('new_customer_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Customer Email -->
                            <div class="mb-4">
                                <label for="new_customer_email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <flux:input id="new_customer_email" wire:model="new_customer_email" type="email"
                                    placeholder="Enter customer email"
                                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                            @error('new_customer_email') border-red-500 dark:border-red-500 @enderror" />
                                @error('new_customer_email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Customer Phone -->
                            <div class="mb-4">
                                <label for="new_customer_phone"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                <flux:input id="new_customer_phone" wire:model="new_customer_phone" type="text"
                                    placeholder="Enter customer phone"
                                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                            @error('new_customer_phone') border-red-500 dark:border-red-500 @enderror" />
                                @error('new_customer_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Customer Address -->
                            <div class="mb-4">
                                <label for="new_customer_address"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                <flux:textarea id="new_customer_address" wire:model="new_customer_address"
                                    placeholder="Enter customer address"
                                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                            @error('new_customer_address') border-red-500 dark:border-red-500 @enderror">
                                </flux:textarea>
                                @error('new_customer_address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Modal Buttons -->
                            <div class="flex justify-end gap-2">
                                <button wire:click="$set('showAddCustomerModal', false)" type="button"
                                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                    Cancel
                                </button>
                                <button wire:click="addCustomer" type="button"
                                    class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- Customer Notes -->
            <div class="mt-3">
                <label for="customer_notes"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Notes</label>
                <flux:textarea id="customer_notes" wire:model="customer_notes"
                    placeholder="Enter any notes about the customer"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
            @error('customer_notes') border-red-500 dark:border-red-500 @enderror">
                </flux:textarea>
                <small class="text-gray-500 dark:text-gray-400">Add any additional notes or instructions for this
                    customer.</small>
                @error('customer_notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-4">

                <button wire:click="nextStep" class="px-6 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                    Next
                </button>
            </div>
        </div>
    @endif

    @if ($currentStep === 2)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Products for Invoice</h2>

            <!-- Search and Select Product -->
            <div class="mt-3">
                <label for="search_product" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search
                    Product</label>
                <div class="flex items-center gap-2">
                    <flux:input id="search_product" wire:click="$set('showStockModal', true)" type="text"
                        placeholder="Search or browse stocks"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                @error('search') border-red-500 dark:border-red-500 @enderror" />
                    <button wire:click="$set('showStockModal', true)" type="button"
                        class="px-4 py-2 bg-gray-900 text-white rounded w-1/3 cursor-pointer hover:bg-gray-800">
                        Browse Stocks
                    </button>
                </div>
                <small class="text-gray-500 dark:text-gray-400">Click the search field or the button to browse
                    stocks.</small>
            </div>

            <!-- Product List -->

            <!-- Stock Modal -->
            @if ($showStockModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-7xl">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Products from
                            Stocks</h2>

                        <!-- Search Field in Modal -->
                        <div class="mb-4">
                            <flux:input id="modal_search_product" wire:model="search" type="text"
                                placeholder="Search by product name or code"
                                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                            @error('search') border-red-500 dark:border-red-500 @enderror" />
                            <small class="text-gray-500 dark:text-gray-400">Search for products by name or
                                code.</small>
                        </div>

                        <!-- Stock Table -->
                        <div
                            class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Product Name</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Stock Code</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Manufactured Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Expiry Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Available Quantity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Quantity to Add</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($products as $product)
                                        <tr>

                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $product->product_name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $product->stock_number }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $product->formatted_manufactured_date }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $product->formatted_expiration_date }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $product->quantity - (collect($cart)->firstWhere('id', $product->id)['quantity'] ?? 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                <flux:input type="number"
                                                    wire:model="quantities.{{ $product->id }}" min="0"
                                                    max=" {{ $product->quantity - (collect($cart)->firstWhere('id', $product->id)['quantity'] ?? 0) }}"
                                                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-20 rounded" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7"
                                                class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No stocks found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal Buttons -->
                        <div class="flex justify-between mt-4">
                            <button wire:click="$set('showStockModal', false)" type="button"
                                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                Close
                            </button>
                            <button wire:click="addSelectedToCart" type="button"
                                class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                                Add Selected to Cart
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Selected Products -->
            @if (count($cart) > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Selected Products</h3>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Stock No.</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Product Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Expiration Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Price per Item</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Total Amount</th>
                                        <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Vatable</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($cart as $index => $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item['stock_number'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item['name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item['expiration_date'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <flux:input type="number" wire:model="cart.{{ $index }}.quantity"
                                                wire:change="updateCartQuantity({{ $index }})" min="1"
                                                max="{{ $item['available_quantity'] }}"
                                                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-20 rounded" />
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            ₱{{ number_format($item['selling_price'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            ₱{{ number_format($item['selling_price'] * $this->getCartQuantity($index), 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                           {{ number_format($item['is_vatable'] ?? 0)  }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <button wire:click="removeProductFromCart({{ $index }})"
                                                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Subtotal:
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        ₱{{ number_format($this->grandTotal, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                        VAT (12%):
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        ₱{{ number_format($this->grandTotal * 0.12, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                                        Grand Total (Incl. VAT):
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100">
                                        ₱{{ number_format($this->grandTotal * 1.12, 2) }}
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            @endif
            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-4">
                <button wire:click="previousStep" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Back
                </button>
                <button wire:click="nextStep" class="px-6 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                    Next
                </button>
            </div>
        </div>
    @endif
    @if ($currentStep === 3)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Details</h2>

        <!-- Tax and Discount -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="tax" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax</label>
                <flux:input id="tax" wire:model="tax" type="number" step="0.01"
                    placeholder="Enter tax percentage or amount"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                    @error('tax') border-red-500 dark:border-red-500 @enderror" />
                <small class="text-gray-500 dark:text-gray-400">Specify the tax for this invoice.</small>
                @error('tax')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="discount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount</label>
                <flux:input id="discount" wire:model="discount" type="number" step="0.01"
                    placeholder="Enter discount amount"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                    @error('discount') border-red-500 dark:border-red-500 @enderror" />
                <small class="text-gray-500 dark:text-gray-400">Apply a discount to this invoice.</small>
                @error('discount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Payment Terms and Assigned Agent -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="payment_terms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Terms</label>
                <flux:select id="payment_terms" wire:model="payment_terms"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded">
                    <flux:select.option value="Net 15">Net 15</flux:select.option>
                    <flux:select.option value="Net 30">Net 30</flux:select.option>
                    <flux:select.option value="Net 60">Net 60</flux:select.option>
                    <flux:select.option value="Net 90">Net 90</flux:select.option>
                </flux:select>
                <small class="text-gray-500 dark:text-gray-400">Select the payment terms for this invoice.</small>
            </div>
            <div>
                <label for="assigned_agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Agent</label>
                <flux:select id="assigned_agent_id" wire:model="assigned_agent_id"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded">
                    <flux:select.option value="0" disabled>Select an agent</flux:select.option>
                    @foreach ($agents as $agent)
                        <flux:select.option value="{{ $agent->id }}">{{ $agent->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <small class="text-gray-500 dark:text-gray-400">Assign this invoice to an agent or staff member.</small>
            </div>
        </div>

        <!-- Invoice Due Date and Invoice Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="invoice_due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Due Date</label>
                <flux:input id="invoice_due_date" wire:model="invoice_due_date" type="date"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                    @error('invoice_due_date') border-red-500 dark:border-red-500 @enderror" />
                <small class="text-gray-500 dark:text-gray-400">Specify the due date for this invoice.</small>
                @error('invoice_due_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="invoice_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Status</label>
                <flux:select id="invoice_status" wire:model="invoice_status"
                    class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded">
                    <flux:select.option value="Pending">Pending</flux:select.option>
                    <flux:select.option value="Paid">Paid</flux:select.option>
                    <flux:select.option value="Overdue">Overdue</flux:select.option>
                </flux:select>
                <small class="text-gray-500 dark:text-gray-400">Set the status of this invoice.</small>
            </div>
        </div>

        <!-- Invoice Notes -->
        <div class="mt-4">
            <label for="invoice_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Notes</label>
            <flux:textarea id="invoice_notes" wire:model="invoice_notes"
                placeholder="Enter any notes about the invoice"
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded
                @error('invoice_notes') border-red-500 dark:border-red-500 @enderror">
            </flux:textarea>
            <small class="text-gray-500 dark:text-gray-400">Add any additional notes or instructions for this invoice.</small>
            @error('invoice_notes')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-between mt-4">
            <button wire:click="previousStep" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                Back
            </button>
            <button wire:click="save" class="px-6 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                Submit
            </button>
        </div>
    </div>
@endif
</div>

</div>
