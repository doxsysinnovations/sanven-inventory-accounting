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
    public $is_vatable = false;
    public $total_vat = 0;
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

    public function mount()
    {
        $this->agents = Agent::all();
        $this->due_date = now()->addDays(7)->format('Y-m-d');
        $this->invoice_date = now()->format('Y-m-d');
        $this->invoice_prefix = config('invoicing.prefix', 'INV');
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

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] += 1;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->product_code,
                'price' => $product->selling_price,
                'cost' => $product->cost_price,
                'is_vatable' => (bool) ($product->is_vatable ?? 0), 
                'vat_tax' => 0, 
                'quantity' => 1,
                'total' => 0,
            ];
        }

        $this->updateCartItem($productId);
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

    public function updateCartItem($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            $quantity = floatval($this->cart[$cartKey]['quantity'] ?? 1);
            $unitPrice = floatval($this->cart[$cartKey]['price'] ?? 0);
            $isVatable = $this->cart[$cartKey]['is_vatable'] ?? false;

            $subtotal = $quantity * $unitPrice;

            if ($isVatable) {
                $vat = $subtotal * 0.12;
                $this->cart[$cartKey]['vat_tax'] = round($vat, 2);
                $this->cart[$cartKey]['total'] = round($subtotal + $vat, 2);
            } else {
                $this->cart[$cartKey]['vat_tax'] = 0;
                $this->cart[$cartKey]['total'] = round($subtotal, 2);
            }
        }
        
        $this->recalculateTotals();
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->recalculateTotals();
    }

   public function recalculateTotals()
    {
        foreach ($this->cart as $cartKey => $item) {
            $quantity = floatval($item['quantity'] ?? 1);
            $unitPrice = floatval($item['price'] ?? 0);
            $isVatable = $item['is_vatable'] ?? false;

            $subtotal = $quantity * $unitPrice;

            if ($isVatable) {
                $vat = $subtotal * 0.12;
                $this->cart[$cartKey]['vat_tax'] = round($vat, 2);
                $this->cart[$cartKey]['total'] = round($subtotal + $vat, 2);
            } else {
                $this->cart[$cartKey]['vat_tax'] = 0;
                $this->cart[$cartKey]['total'] = round($subtotal, 2);
            }
        }

        $this->subtotal = collect($this->cart)->sum('total');
        $this->tax = collect($this->cart)->sum('vat_tax');
        $this->total_vat = $this->tax;
        
        $discountAmount = $this->getTotalDiscountProperty();
        $this->total = floatval(sprintf('%.2f', $this->subtotal - $discountAmount));
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

    public function getSubtotalProperty()
    {
        return (float)collect($this->cart)->sum('total');
    }

    public function getTotalDiscountProperty()
    {
        $discount = (float)$this->discount;
        $baseTotal = $this->getSubtotalProperty();

        if ($this->discount_type === 'percentage') {
            $this->total_discount = $baseTotal * ($discount / 100);
        } else {
            $this->total_discount = $discount;
        }

        return $this->total_discount;
    }
    
    public function getTotalProperty()
    {
        $baseTotal = $this->getSubtotalProperty();
        $discountAmount = $this->getTotalDiscountProperty();
        
        return floatval(sprintf('%.2f', $baseTotal - $discountAmount));
    }

    public function getProfitEstimateProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return ($item['price'] - $item['cost']) * $item['quantity'];
        });
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

                $cartKey = 'stock-' . $stockId;

                if (isset($this->cart[$cartKey])) {
                    $this->cart[$cartKey]['quantity'] += $quantityToAdd;
                } else {
                    $this->cart[$cartKey] = [
                        'id' => $stock->product->id,
                        'stock_id' => $stock->id,
                        'name' => $stock->product->name,
                        'code' => $stock->product->product_code,
                        'price' => $stock->selling_price ?? $stock->product->selling_price,
                        'cost' => $stock->capital_price ?? $stock->product->cost_price,
                        'is_vatable' => (bool) ($stock->product->is_vatable ?? 0), 
                        'vat_tax' => 0,
                        'quantity' => $quantityToAdd,
                        'available_quantity' => $availableQuantity,
                        'stock_number' => $stock->stock_number,
                        'expiration_date' => $stock->expiration_date?->format('Y-m-d'),
                        'batch_number' => $stock->batch_number,
                        'total' => 0,
                    ];
                }
                
                $this->updateCartItem($cartKey);
            }
        }

        $this->selectedProducts = [];
        $this->productQuantities = [];
        $this->showProductModal = false;

        $this->recalculateTotals();
        $this->dispatch('notify', type: 'success', message: 'Selected products added to cart successfully!');
    }

    public function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::latest()->first();
        $number = $lastInvoice ? (int) str_replace($this->invoice_prefix, '', $lastInvoice->invoice_number) + 1 : 1;
        return $this->invoice_prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function submitInvoice()
    {
        $this->isLoading = true;

        $validated = $this->validate([
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,paypal,other',
            'due_date' => 'required|date|after_or_equal:today',
            'invoice_date' => 'required|date',
            'payment_terms' => 'required|string',
            'discount' => 'nullable|numeric|min:0|max:1000000',
            'tax' => 'nullable|numeric|min:0|max:1000000',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Create the invoice
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $this->customer_id,
                'total_amount' => $this->subtotal,
                'discount' => $this->total_discount,
                'tax' => $this->tax,
                'grand_total' => $this->total,
                'status' => 'pending',
                'payment_terms' => $this->payment_terms,
                'payment_method' => $this->payment_method,
                'due_date' => $this->due_date,
                'issued_date' => $this->invoice_date,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
                'agent_id' => $this->assigned_agent,
            ]);
            
            // Add invoice items
            foreach ($this->cart as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'stock_id' => $item['stock_id'] ?? null,
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'total' => $item['total'],
                    // Include these if they exist in your cart items
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['vat_tax'] ?? 0,
                ]);

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
            $this->resetExcept('invoice_prefix', 'currentStep');
            $this->due_date = now()->addDays(7)->format('Y-m-d');
            $this->invoice_date = now()->format('Y-m-d');
            $this->currentStep = 1;

            $this->dispatch('invoice-created');
            session()->flash('message', [
                'type' => 'success',
                'title' => 'Invoice Created',
                'message' => 'The invoice has been created successfully!',
                'invoiceId' => $invoice->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
             dd('exception', $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error creating invoice: ' . $e->getMessage());
            logger()->error('Invoice creation error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedSearchProduct()
    {
        $this->loadStocks();
    }
}; ?>

<div>
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

    <div>
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                Create Invoice
            </h3>
        </div>

        <div class="bg-white dark:bg-(--color-accent-dark) p-8 sm:p-10">
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
                                <div class="text-xs">& Finalize</div>
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
                                        <span class="text-gray-600 dark:text-gray-300">Vatable Amount:</span>
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

                <div class="md:col-span-3 bg-white dark:bg-gray-800">
                    @include('livewire.invoicing.views.step-1-customer-information')
                    @include('livewire.invoicing.views.step-2-add-products')
                    @include('livewire.invoicing.views.step-3-review-invoice')
                </div>
            </div>
        </div>
    </div>
</div>
