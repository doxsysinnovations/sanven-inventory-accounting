<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Stock;
use App\Models\Agent;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

new class extends Component {
    use WithFileUploads;

    public Invoice $invoice;
    public $products = [];
    public $lastInvoice = null;

    // Step tracking
    public $currentStep = 1;
    public $totalSteps = 3;

    // Step 1: Customer Information
    #[Validate('required')]
    public $customer_id = '';
    public $searchCustomer = '';
    public $showCustomerForm = false;
    #[Validate('required|string|max:255')]
    public $name = '';
    #[Validate('required|email|max:255|unique:customers,email')]
    public $email = '';
    #[Validate('nullable|string|max:20')]
    public $phone = '';
    #[Validate('nullable|string')]
    public $address = '';

    // Step 2: Product Information
    public $searchProduct = '';
    public $cart = [];
    #[Validate('nullable|string|max:1000')]
    public $notes = '';
    public $showBulkAddModal = false;
    public $bulkProducts = '';

    // Step 3: Review & Create
    #[Validate('required|string|in:cash,credit_card,bank_transfer,paypal,other')]
    public $payment_method = 'cash';
    #[Validate('required|date|after_or_equal:today')]
    public $due_date;
    #[Validate('nullable|numeric|min:0|max:1000000')]
    public $discount = 0;
    #[Validate('nullable|numeric|min:0|max:1000000')]
    public $tax = 0;
    #[Validate('nullable|numeric|between:0,100')]
    public $tax_rate = 0;
    public $discount_type = 'fixed'; // 'fixed' or 'percentage'
    public $invoice_prefix = 'INV';
    public $invoice_date;
    public $terms_conditions = 'Payment due within 7 days. Late payments subject to 1.5% monthly interest.';
    public $payment_terms = '';
    public $assigned_agent = '';

    // UI State
    public $showProductModal = false;
    public $selectedProducts = [];
    public $productQuantities = [];
    public $isLoading = false;

    public $agents = [];

    public $subtotal = 0;
    public $total = 0;
    public $total_discount = 0;

    public function mount(Invoice $invoice) // Use route model binding
    {
        $this->invoice = $invoice->load(['customer', 'items.stock.product', 'items.stock', 'agent']);

        // Initialize form with invoice data
        $this->customer_id = $this->invoice->customer_id;
        $this->payment_method = $this->invoice->payment_method;
        $this->due_date = is_string($this->invoice->due_date) ? $this->invoice->due_date : $this->invoice->due_date?->format('Y-m-d');
        $this->invoice_date = is_string($this->invoice->issued_date) ? $this->invoice->issued_date : $this->invoice->issued_date?->format('Y-m-d');
        $this->notes = $this->invoice->notes;
        $this->subtotal = $this->invoice->total_amount;
        $this->discount = $this->invoice->discount;
        $this->tax = $this->invoice->tax;
        $this->total = $this->invoice->grand_total;
        $this->payment_terms = $this->invoice->payment_terms;
        $this->assigned_agent = $this->invoice->agent_id;
        $this->terms_conditions = $this->invoice->terms_conditions ?? $this->terms_conditions;

        // Initialize cart with invoice items
        foreach ($this->invoice->items as $item) {
            $cartKey = $item->stock_id ? 'stock-' . $item->stock_id : 'product-' . $item->product_id;

            $this->cart[$cartKey] = [
                'id' => $item->product_id,
                'stock_id' => $item->stock_id,
                'name' => $item->product_name,
                'code' => $item->product->product_code ?? '',
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->total,
                'available_quantity' => $item->stock ? $item->stock->quantity + $item->quantity : ($item->product->stock_quantity + $item->quantity),
                'stock_number' => $item->stock->stock_number ?? '',
                'expiration_date' => $item->stock->expiration_date?->format('Y-m-d') ?? '',
                'batch_number' => $item->stock->batch_number ?? '',
            ];
        }

        $this->agents = Agent::all();
        $this->loadStocks();
    }

    // ========== STEP 1 METHODS ==========
    public function customers()
    {
        return Customer::query()
            ->when($this->searchCustomer, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchCustomer . '%')
                        ->orWhere('email', 'like', '%' . $this->searchCustomer . '%')
                        ->orWhere('phone', 'like', '%' . $this->searchCustomer . '%');
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function addCustomer()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);
        $this->customer_id = $customer->id;
        $this->showCustomerForm = false;
        $this->reset(['name', 'email', 'phone', 'address']);

        $this->dispatch('customer-added', customerId: $customer->id);
    }

    public function goToStep2()
    {
        $this->validate(['customer_id' => 'required|exists:customers,id']);
        $this->currentStep = 2;
    }

    // ========== STEP 2 METHODS ==========

    public function loadStocks()
    {
        $this->products = Stock::with(['product'])
            ->whereHas('product', function ($query) {
                $query->when($this->searchProduct, function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%' . $this->searchProduct . '%')
                            ->orWhere('product_code', 'like', '%' . $this->searchProduct . '%')
                            ->orWhere('description', 'like', '%' . $this->searchProduct . '%');
                    });
                });
            })
            ->orderByRaw(
                "
        CASE
            WHEN expiration_date < CURDATE() THEN 1
            WHEN expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 2
            ELSE 3
        END, expiration_date ASC
    ",
            )
            ->limit(10)
            ->get();
    }

    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);

        $cartKey = 'product-' . $productId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] += 1;
        } else {
            $this->cart[$cartKey] = [
                'id' => $product->id,
                'stock_id' => null,
                'name' => $product->name,
                'code' => $product->product_code,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
                'available_quantity' => $product->stock_quantity,
            ];
        }

        $this->updateCartItem($cartKey);
    }

    public function updateProductSelection($stockId)
    {
        $quantity = $this->productQuantities[$stockId] ?? 0;

        if ($quantity > 0 && !in_array($stockId, $this->selectedProducts)) {
            $this->selectedProducts[] = $stockId;
        } elseif ($quantity <= 0 && in_array($stockId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$stockId]);
        }
    }

    public function updateCartItem($productKey)
    {
        if (isset($this->cart[$productKey])) {
            $this->cart[$productKey]['total'] = $this->cart[$productKey]['price'] * $this->cart[$productKey]['quantity'];
        }
        $this->recalculateTotals();
    }

    public function removeFromCart($productKey)
    {
        unset($this->cart[$productKey]);
        $this->recalculateTotals();
    }

    public function recalculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum('total');

        // Calculate discount
        $this->total_discount = $this->discount_type === 'percentage'
            ? $this->subtotal * ($this->discount / 100)
            : $this->discount;

        // Calculate tax based on tax rate if provided
        if ($this->tax_rate > 0) {
            $this->tax = ($this->subtotal - $this->total_discount) * ($this->tax_rate / 100);
        }

        $this->total = $this->subtotal - $this->total_discount + $this->tax;
    }

    public function updatedTaxRate($value)
    {
        $this->recalculateTotals();
    }

    public function updatedDiscount($value)
    {
        $this->recalculateTotals();
    }

    public function updatedDiscountType()
    {
        $this->discount = 0;
        $this->recalculateTotals();
    }

    public function backToStep1()
    {
        $this->currentStep = 1;
    }

    public function goToStep3()
    {
        $this->validate(['cart' => 'required|array|min:1']);
        $this->currentStep = 3;
    }

    // ========== STEP 3 METHODS ==========
    public function backToStep2()
    {
        $this->currentStep = 2;
    }

    public function openProductModal()
    {
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->selectedProducts = [];
        $this->productQuantities = [];
    }

    public function toggleProductSelection($productId)
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
        } else {
            $this->selectedProducts[] = $productId;
            $this->productQuantities[$productId] = 1; // Default quantity
        }
    }

    public function addSelectedProducts()
    {
        foreach ($this->selectedProducts as $stockId) {
            $stock = Stock::with('product')->find($stockId);

            if ($stock && $stock->product) {
                $quantityToAdd = $this->productQuantities[$stockId] ?? 1;
                $availableQuantity = $stock->quantity - collect($this->cart)->where('stock_id', $stockId)->sum('quantity');

                if ($quantityToAdd <= 0) {
                    continue;
                }

                if ($quantityToAdd > $availableQuantity) {
                    $this->dispatch('notify', type: 'error', message: "Cannot add more than available stock for {$stock->product->name} (Stock #: {$stock->stock_number}). Available: {$availableQuantity}");
                    continue;
                }

                // Create a unique key for this stock item
                $cartKey = 'stock-' . $stockId;

                if (isset($this->cart[$cartKey])) {
                    // Update existing item
                    $this->cart[$cartKey]['quantity'] += $quantityToAdd;
                    $this->cart[$cartKey]['total'] = $this->cart[$cartKey]['price'] * $this->cart[$cartKey]['quantity'];
                } else {
                    // Add new item
                    $this->cart[$cartKey] = [
                        'id' => $stock->product->id,
                        'stock_id' => $stock->id,
                        'name' => $stock->product->name,
                        'code' => $stock->product->product_code,
                        'price' => $stock->selling_price ?? $stock->product->selling_price,
                        'cost' => $stock->capital_price ?? $stock->product->cost_price,
                        'quantity' => $quantityToAdd,
                        'available_quantity' => $availableQuantity,
                        'stock_number' => $stock->stock_number,
                        'expiration_date' => $stock->expiration_date?->format('Y-m-d'),
                        'batch_number' => $stock->batch_number,
                        'total' => ($stock->selling_price ?? $stock->product->selling_price) * $quantityToAdd,
                    ];
                }
            }
        }

        // Clear selection and close modal
        $this->selectedProducts = [];
        $this->productQuantities = [];
        $this->showProductModal = false;

        // Recalculate totals
        $this->recalculateTotals();
        $this->dispatch('notify', type: 'success', message: 'Selected products added to cart successfully!');
    }

    public function submitInvoice()
    {
        $this->isLoading = true;

        $validated = $this->validate([
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,paypal,other',
            'due_date' => 'required|date',
            'invoice_date' => 'required|date',
            'payment_terms' => 'required|string',
            'assigned_agent' => 'required|exists:agents,id',
            'discount' => 'nullable|numeric|min:0|max:1000000',
            'tax' => 'nullable|numeric|min:0|max:1000000',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // First, restore stock quantities from the original invoice items
            foreach ($this->invoice->items as $item) {
                if ($item->stock_id) {
                    $stock = Stock::find($item->stock_id);
                    if ($stock) {
                        $stock->increment('quantity', $item->quantity);
                    }
                }

                $product = Product::find($item->product_id);
                if ($product && $product->track_stock) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }

            // Delete all existing invoice items
            $this->invoice->items()->delete();

            // Update the invoice
            $this->invoice->update([
                'customer_id' => $this->customer_id,
                'total_amount' => $this->subtotal,
                'discount' => $this->total_discount,
                'tax' => $this->tax,
                'grand_total' => $this->total,
                'status' => 'pending',
                'payment_method' => $this->payment_method,
                'due_date' => $this->due_date,
                'issued_date' => $this->invoice_date,
                'notes' => $this->notes,
                'agent_id' => $this->assigned_agent,
                'payment_terms' => $this->payment_terms,
                'terms_conditions' => $this->terms_conditions,
            ]);

            // Add new invoice items
            foreach ($this->cart as $item) {
                $invoiceItem = [
                    'invoice_id' => $this->invoice->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'total' => $item['total'],
                ];

                if (isset($item['stock_id'])) {
                    $invoiceItem['stock_id'] = $item['stock_id'];
                }

                InvoiceItem::create($invoiceItem);

                // Update product stock if stock_id exists
                if (isset($item['stock_id'])) {
                    $stock = Stock::find($item['stock_id']);
                    if ($stock) {
                        $stock->decrement('quantity', $item['quantity']);
                    }
                }

                // Update product stock quantity if tracking is enabled
                $product = Product::find($item['id']);
                if ($product && $product->track_stock) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            DB::commit();

            // Reset form
            $this->dispatch('invoice-updated');
            session()->flash('message', [
                'type' => 'success',
                'title' => 'Invoice Updated',
                'message' => 'The invoice has been updated successfully!',
                'invoiceId' => $this->invoice->id,
            ]);

            return redirect()->route('invoices.show', $this->invoice->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Error updating invoice: ' . $e->getMessage());
            logger()->error('Invoice update error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatingSearchProduct()
    {
        $this->loadStocks();
    }
};
?>

<!-- The view part remains the same -->
<div>
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                        </svg>
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
                        <a href="{{ route('invoicing') }}"
                            class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 md:ml-2">Invoices</a>
                    </div>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 md:ml-2">Edit Invoice
                            #{{ $invoice->invoice_number }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    @if (session('message'))
        <div @class([
            'mb-4 p-4 rounded-lg',
            'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100' =>
                session('message.type') === 'success',
            'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100' =>
                session('message.type') === 'error',
        ])>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if (session('message.type') === 'success')
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                    <span class="font-medium">{{ session('message.title') }}</span>
                </div>
                @if (isset(session('message')->invoiceId))
                    <a href="{{ route('invoices.show', session('message')->invoiceId) }}"
                        class="text-sm underline hover:no-underline">
                        View Invoice
                    </a>
                @endif
            </div>
            <p class="mt-1 ml-7 text-sm">{{ session('message.message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Left Sidebar - Steps -->
        <div class="md:col-span-1">
            <div class="space-y-2">
                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg transition-colors',
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' =>
                        $currentStep === 1,
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' =>
                        $currentStep !== 1,
                ])>
                    <div @class([
                        'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                        'bg-blue-600 text-white' => $currentStep === 1,
                        'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' =>
                            $currentStep !== 1,
                    ])>1</div>
                    <div>
                        <div class="font-medium">Customer</div>
                        <div class="text-xs">Information</div>
                    </div>
                </div>

                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg transition-colors',
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' =>
                        $currentStep === 2,
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' =>
                        $currentStep !== 2,
                ])>
                    <div @class([
                        'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                        'bg-blue-600 text-white' => $currentStep === 2,
                        'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' =>
                            $currentStep !== 2,
                    ])>2</div>
                    <div>
                        <div class="font-medium">Products</div>
                        <div class="text-xs">Selection</div>
                    </div>
                </div>

                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg transition-colors',
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' =>
                        $currentStep === 3,
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' =>
                        $currentStep !== 3,
                ])>
                    <div @class([
                        'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                        'bg-blue-600 text-white' => $currentStep === 3,
                        'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' =>
                            $currentStep !== 3,
                    ])>3</div>
                    <div>
                        <div class="font-medium">Review</div>
                        <div class="text-xs">& Update</div>
                    </div>
                </div>
            </div>

            <!-- Progress Summary -->
            @if ($currentStep > 1)
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-sm font-medium mb-2">Invoice Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Items:</span>
                            <span>{{ count($cart) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                            <span>Php {{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if ($discount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Discount:</span>
                                <span class="text-red-600 dark:text-red-400">-
                                    Php {{ number_format($total_discount, 2) }}</span>
                            </div>
                        @endif
                        @if ($tax > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Tax:</span>
                                <span>+ Php {{ number_format($tax, 2) }}</span>
                            </div>
                        @endif
                        <div
                            class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600 font-medium">
                            <span>Total:</span>
                            <span>Php {{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <div class="md:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <!-- Step 1: Customer Information -->
            @if ($currentStep === 1)
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold">Step 1: Customer Information</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Select an existing customer or add a new one
                        </p>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Required fields <span class="text-red-500">*</span>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search
                        Customers</label>
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="searchCustomer" type="text"
                            placeholder="Search by name, email or phone..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                @if ($searchCustomer)
                    <div class="space-y-2 mb-6 max-h-96 overflow-y-auto">
                        @forelse($this->customers() as $customer)
                            <div wire:key="customer-{{ $customer->id }}"
                                class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                wire:click="$set('customer_id', '{{ $customer->id }}')">
                                <div>
                                    <div class="font-medium">{{ $customer->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}
                                    </div>
                                    @if ($customer->company_name)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $customer->company_name }}</div>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    @if ($customer_id == $customer->id)
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-gray-500 dark:text-gray-400">
                                No customers found matching your search
                            </div>
                        @endforelse
                    </div>
                @endif

                <div class="flex items-center justify-between mb-6">
                    <button wire:click="$set('showCustomerForm', true)" type="button"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Customer
                    </button>

                    @if ($customer_id)
                        <button wire:click="goToStep2" type="button"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                            Continue to Products
                        </button>
                    @endif
                </div>

                @if ($showCustomerForm)
                    <div class="space-y-4 mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <h3 class="text-md font-medium">New Customer Details</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer
                                    Name <span class="text-red-500">*</span></label>
                                <input wire:model="name" type="text"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                @error('name')
                                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                    <span class="text-red-500">*</span></label>
                                <input wire:model="email" type="email"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                @error('email')
                                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                <input wire:model="phone" type="tel"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                @error('phone')
                                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                                <textarea wire:model="address" rows="2"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"></textarea>
                                @error('address')
                                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-between pt-4">
                            <button wire:click="$set('showCustomerForm', false)" type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                                Cancel
                            </button>
                            <button wire:click="addCustomer" type="button"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                                Save Customer
                            </button>
                        </div>
                    </div>
                @endif

                @if ($customer_id && !$showCustomerForm)
                    @php $selectedCustomer = Customer::find($customer_id); @endphp
                    <div
                        class="mb-6 p-4 border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium">{{ $selectedCustomer->name }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">{{ $selectedCustomer->email }}
                                </div>
                                @if ($selectedCustomer->company_name)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $selectedCustomer->company_name }}</div>
                                @endif
                                @if ($selectedCustomer->phone)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $selectedCustomer->phone }}</div>
                                @endif
                            </div>
                            <button wire:click="$set('customer_id', '')"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline transition-colors">
                                Change
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Step 2: Product Information -->
            @elseif($currentStep === 2)
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold">Step 2: Add Products</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Search and add products to the invoice</p>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ count($cart) }} items | Php {{ number_format($subtotal, 2) }}
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-6">
                    <button wire:click="openProductModal" type="button"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Products
                    </button>

                    @if (count($cart) > 0)
                        <button wire:click="goToStep3" type="button"
                            class="ml-auto px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 transition-colors">
                            Review Invoice
                        </button>
                    @endif
                </div>

                <!-- Stock Modal -->
                @if ($showProductModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800/75 backdrop-blur-sm">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-7xl mx-4">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    Select Products from Stock
                                </h2>
                                <button wire:click="$set('showProductModal', false)"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="relative mb-6">
                                <input id="modal_search_product" wire:model.live="searchProduct" type="text"
                                    placeholder="Search by product name or code"
                                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200" />

                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    <input type="checkbox" wire:model="selectAllProducts"
                                                        class="rounded">
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Product Name
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Stock #
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Batch #
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Expiry Date
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Available
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Price
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Qty to Add
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse ($products as $stock)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="checkbox" wire:model="selectedProducts"
                                                            value="{{ $stock->id }}" class="rounded"
                                                            @if (isset($productQuantities[$stock->id]) && $productQuantities[$stock->id] > 0) checked @endif>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $stock->product->name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $stock->product->product_code }}
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $stock->stock_number }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $stock->batch_number }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $stock->expiration_date?->format('Y-m-d') ?? 'N/A' }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $stock->quantity - collect($cart)->where('stock_id', $stock->id)->sum('quantity') }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ number_format($stock->selling_price ?? $stock->product->selling_price, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="number"
                                                            wire:model="productQuantities.{{ $stock->id }}"
                                                            min="1"
                                                            max="{{ $stock->quantity - collect($cart)->where('stock_id', $stock->id)->sum('quantity') }}"
                                                            class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-2 py-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"
                                                            wire:change="updateProductSelection('{{ $stock->id }}')">
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8"
                                                        class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                        No stocks found matching your criteria
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <button wire:click="$set('showProductModal', false)" type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors">
                                    Cancel
                                </button>
                                <button wire:click="addSelectedProducts" type="button"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors">
                                    Add Selected to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if (count($cart) > 0)
                    <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stock #
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Qty
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($cart as $key => $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $item['name'] }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $item['code'] }}
                                                    @if ($item['expiration_date'])
                                                        | Exp: {{ $item['expiration_date'] }}
                                                    @endif
                                                    @if ($item['batch_number'])
                                                        | Batch: {{ $item['batch_number'] }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item['stock_number'] ?? 'N/A' }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ number_format($item['price'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="number" wire:model="cart.{{ $key }}.quantity"
                                                    min="1"
                                                    max="{{ $item['available_quantity'] + $item['quantity'] }}"
                                                    class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-2 py-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"
                                                    wire:change="updateCartItem('{{ $key }}')">
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ number_format($item['total'], 2) }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <button wire:click="removeFromCart('{{ $key }}')"
                                                    class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes
                            (Internal)</label>
                        <textarea wire:model="notes" rows="3"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"
                            placeholder="Any internal notes about this invoice..."></textarea>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p>No products added to invoice yet</p>
                        <button wire:click="openProductModal" type="button"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                            Add Products
                        </button>
                    </div>
                @endif

                <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="backToStep1" type="button"
                        class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Back
                    </button>
                    <button wire:click="goToStep3" type="button"
                        class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors disabled:opacity-50"
                        @if (count($cart) === 0) disabled @endif>
                        Review Invoice
                    </button>
                </div>

                <!-- Step 3: Review & Create -->
            @elseif($currentStep === 3)
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold">Step 3: Review Invoice</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Review details and update the invoice</p>
                    </div>
                    <div class="text-sm font-medium">
                        Total: <span class="text-blue-600 dark:text-blue-400">Php
                            {{ number_format($total, 2) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-md font-medium mb-3">Customer Information</h3>
                        @php $customer = Customer::find($customer_id); @endphp
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="font-medium">{{ $customer->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}</div>
                            @if ($customer->phone)
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->phone }}</div>
                            @endif
                            @if ($customer->company_name)
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->company_name }}
                                </div>
                            @endif
                            @if ($customer->address)
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $customer->address }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-md font-medium mb-3">Invoice Details</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Invoice
                                        Date</label>
                                    <input wire:model="invoice_date" type="date"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                    @error('invoice_date')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due
                                        Date</label>
                                    <input wire:model="due_date" type="date"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                    @error('due_date')
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment
                                    Method</label>
                                <select wire:model="payment_method"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('payment_method')
                                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Terms and Assigned Agent -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="payment_terms"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Terms</label>
                        <select id="payment_terms" wire:model="payment_terms"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                            <option value="">-- Select Payment Terms --</option>
                            <option value="Net 15">Net 15</option>
                            <option value="Net 30">Net 30</option>
                            <option value="Net 60">Net 60</option>
                            <option value="Net 90">Net 90</option>
                        </select>
                        <small class="text-gray-500 dark:text-gray-400">Select the payment terms for this
                            invoice.</small>
                    </div>
                    <div>
                        <label for="assigned_agent_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Agent</label>
                        <select id="assigned_agent_id" wire:model="assigned_agent"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                            <option value="">-- Select Agent --</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-gray-500 dark:text-gray-400">Assign this invoice to an agent or staff
                            member.</small>
                    </div>
                </div>

                <h3 class="text-md font-medium mb-3">Invoice Items</h3>
                <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Product</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Price</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Qty</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($cart as $item)
                                    <tr wire:key="summary-item-{{ $item['id'] }}"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item['name'] }}</div>
                                            @if ($item['code'])
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $item['code'] }}</div>
                                            @endif
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            Php {{ number_format($item['price'], 2) }}</td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item['quantity'] }}</td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            Php {{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="space-y-2">
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                            <span class="font-medium">Php {{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between py-2">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-600 dark:text-gray-300">Discount:</span>
                                <select wire:model="discount_type"
                                    class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-2 py-1 text-gray-900 dark:text-gray-100">
                                    <option value="fixed">Fixed</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                                <input wire:model.lazy="discount" type="number" min="0"
                                    @if ($discount_type === 'percentage') max="100" @endif step="0.01"
                                    class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-2 py-1 text-sm text-gray-900 dark:text-gray-100">
                                @if ($discount_type === 'percentage')
                                    <span class="text-xs text-gray-500 dark:text-gray-400">%</span>
                                @endif
                            </div>
                            <span class="font-medium text-red-600 dark:text-red-400">-
                                Php {{ number_format($total_discount, 2) }}</span>
                        </div>

                        <div class="flex justify-between py-2">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-600 dark:text-gray-300">Tax:</span>
                                <input wire:model.lazy="tax_rate" type="number" min="0" max="100"
                                    step="0.01"
                                    class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-2 py-1 text-sm text-gray-900 dark:text-gray-100">
                                <span class="text-xs text-gray-500 dark:text-gray-400">%</span>
                            </div>
                            <span class="font-medium">+ Php {{ number_format($tax, 2) }}</span>
                        </div>

                        <div
                            class="flex justify-between py-2 border-t border-gray-200 dark:border-gray-700 mt-2 font-medium text-lg">
                            <span>Total:</span>
                            <span>Php {{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terms &
                        Conditions</label>
                    <textarea wire:model="terms_conditions" rows="3"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"></textarea>
                </div>

                @if ($notes)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Internal Notes</h4>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-sm">
                            {{ $notes }}
                        </div>
                    </div>
                @endif

                <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="backToStep2" type="button"
                        class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Back
                    </button>
                    <div class="flex gap-2">
                        <button onclick="window.print()" type="button"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                            Print Invoice
                        </button>
                        <button wire:click="submitInvoice" type="button"
                            class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 transition-colors flex items-center gap-2"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Update Invoice</span>
                            <span wire:loading>Processing...</span>
                            <svg wire:loading class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
