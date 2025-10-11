<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;

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
    public $invoice_status;
    public $terms_conditions = "All sales are subject to the following terms and conditions:
1. Prices, discounts, and VAT are subject to change until order confirmation.
2. Payment terms as agreed and stated in this Sales Order.
3. Delivery dates are estimates and subject to stock availability.
4. Goods delivered are non-returnable unless defective or not as ordered.
5. Any claims must be made within 3 days of delivery.
6. Ownership transfers upon full payment.
7. The company reserves the right to cancel orders due to unforeseen circumstances.
8. Other terms as may be agreed in writing.";
    public $payment_terms = 'Cash';
    public $assigned_agent = '';
    public $partial_payment_amount = null;
    // UI State
    public $showProductModal = false;
    public $selectedProducts = [];
    public $productQuantities = [];
    public $isLoading = false;

    public $agents = [];

    public $subtotal = 0;
    public $total = 0;
    public $total_discount = 0;

    public $showPrintPreview = false;
    public $loadingPDFPreview = false;
    public $invoice = null;
    public $order_date;
    public $requested_delivery_date;

    public function mount()
    {
        $this->agents = Agent::all();
        $this->due_date = now()->addDays(7)->format('Y-m-d');
        $this->invoice_date = now()->format('Y-m-d');
        $this->invoice_prefix = config('invoicing.prefix', 'INV');
        $this->loadProducts();

        $this->order_date = now()->format('Y-m-d'); // now as default
        $this->requested_delivery_date = $this->calculateDeliveryDate($this->order_date);
    }

    public function calculateDeliveryDate($startDate)
    {
        $date = Carbon::parse($startDate);
        $daysToAdd = 1;
        $currentDay = 0;

        while ($currentDay < $daysToAdd) {
            $date->addDay();
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $currentDay++;
            }
        }

        return $date->format('Y-m-d');
    }

    public function updatedOrderDate()
    {
        $this->requested_delivery_date = $this->calculateDeliveryDate($this->order_date);
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

    // public function loadStocks()
    // {
    //     $this->products = Stock::with(['product'])
    //         ->whereHas('product', function ($query) {
    //             $query->when($this->searchProduct, function ($query) {
    //                 $query->where(function ($q) {
    //                     $q->where('name', 'like', '%' . $this->searchProduct . '%')
    //                         ->orWhere('product_code', 'like', '%' . $this->searchProduct . '%')
    //                         ->orWhere('description', 'like', '%' . $this->searchProduct . '%');
    //                 });
    //             });
    //         })
    //         ->orderByRaw(
    //             "
    //     CASE
    //         WHEN expiration_date < CURDATE() THEN 1
    //         WHEN expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 2
    //         ELSE 3
    //     END, expiration_date ASC
    // ",
    //         )
    //         ->limit(10)
    //         ->get();
    // }

    public function loadProducts()
    {
        $this->products = Product::query()
            ->when($this->searchProduct, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchProduct . '%')
                        ->orWhere('product_code', 'like', '%' . $this->searchProduct . '%')
                        ->orWhere('description', 'like', '%' . $this->searchProduct . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    // public function addToCart($productId)
    // {
    //     $product = Product::findOrFail($productId);

    //     if (isset($this->cart[$productId])) {
    //         $this->cart[$productId]['quantity'] += 1;
    //     } else {
    //         $this->cart[$productId] = [
    //             'id' => $product->id,
    //             'name' => $product->name,
    //             'code' => $product->product_code,
    //             'price' => $product->selling_price,
    //             'cost' => $product->cost_price,
    //             'is_vatable' => (bool) ($product->is_vatable ?? 0),
    //             'vat_tax' => 0,
    //             'quantity' => 1,
    //             'total' => 0,
    //         ];
    //     }

    //     $this->updateCartItem($productId);
    // }

    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] += 1;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'strength' => $product->strength ?? '',
                'unit' => $product->unit->name ?? '',
                'type' => $product->type->name ?? '',
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
        if (!isset($this->cart[$cartKey])) {
            return;
        }

        $rawQuantity = floatval($this->cart[$cartKey]['quantity'] ?? 1);
        if (!is_numeric($rawQuantity) || $rawQuantity < 1) {
            $rawQuantity = 1;
            $this->cart[$cartKey]['quantity'] = 1;
        }

        $quantity = floatval($rawQuantity);
        $unitPrice = floatval($this->cart[$cartKey]['price'] ?? 0);
        $isVatable = $this->cart[$cartKey]['is_vatable'] ?? false;
        $stockId = $this->cart[$cartKey]['stock_id'] ?? null;

        // Check against available stock
        if ($stockId) {
            $stock = Stock::find($stockId);
            if ($stock) {
                $availableQuantity = $stock->quantity;

                // Adjust for other quantities of the same stock in cart (optional)
                $otherQuantity = collect($this->cart)->except($cartKey)->where('stock_id', $stockId)->sum('quantity');

                $remainingStock = $availableQuantity - $otherQuantity;

                if ($quantity > $remainingStock) {
                    $productName = $product->name ?? 'Unknown';
                    $stockNumber = $stock->stock_number ?? 'N/A';

                    flash()->warning("Only {$remainingStock} available for {$productName} (Stock #: {$stockNumber}). Quantity has been adjusted.");

                    $quantity = $remainingStock;
                    $this->cart[$cartKey]['quantity'] = $quantity;
                }
            }
        }

        // Compute subtotal, VAT, and total
        $subtotal = $quantity * $unitPrice;

        if ($isVatable) {
            $vat = $subtotal * 0.12;
            $this->cart[$cartKey]['vat_tax'] = round($vat, 2);
            $this->cart[$cartKey]['total'] = round($subtotal + $vat, 2);
        } else {
            $this->cart[$cartKey]['vat_tax'] = 0;
            $this->cart[$cartKey]['total'] = round($subtotal, 2);
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
                // VAT-inclusive: extract VAT from total
                $vat = ($subtotal / 1.12) * 0.12;
                $this->cart[$cartKey]['vat_tax'] = round($vat, 2);
                $this->cart[$cartKey]['total'] = round($subtotal, 2); // No extra VAT added
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
        return (float) collect($this->cart)->sum('total');
    }

    public function getTotalDiscountProperty()
    {
        $discount = (float) $this->discount;
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
        if (empty($this->cart) || count($this->cart) < 1) {
            flash()->error('No products selected. Please add at least one product to continue.');
            return;
        }
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
            $this->productQuantities[$productId] = 1;
        }
    }

    public function addSelectedProducts()
    {
        $addedCount = 0;
        $skippedMessages = [];

        foreach ($this->selectedProducts as $stockId) {
            $stock = Stock::with('product')->find($stockId);

            if ($stock && $product) {
                $quantityToAdd = $this->productQuantities[$stockId] ?? 1;
                $availableQuantity = $stock->quantity - collect($this->cart)->where('stock_id', $stockId)->sum('quantity');

                if ($quantityToAdd <= 0) {
                    continue;
                }

                if ($quantityToAdd > $availableQuantity) {
                    $skippedMessages[] = "❌ {$product->name} (Stock #: {$stock->stock_number}) - Available: {$availableQuantity}";
                    continue;
                }

                $cartKey = 'stock-' . $stockId;

                if (isset($this->cart[$cartKey])) {
                    $this->cart[$cartKey]['quantity'] += $quantityToAdd;
                } else {
                    $this->cart[$cartKey] = [
                        'id' => $product->id,
                        'stock_id' => $stock->id,
                        'name' => $product->name,
                        'code' => $product->product_code,
                        'price' => $stock->selling_price ?? $product->selling_price,
                        'cost' => $stock->capital_price ?? $product->cost_price,
                        'is_vatable' => (bool) ($product->is_vatable ?? 0),
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
                $addedCount++;
            }
        }

        $this->selectedProducts = [];
        $this->productQuantities = [];
        $this->showProductModal = false;

        $this->recalculateTotals();

        if ($addedCount > 0 && count($skippedMessages) === 0) {
            flash()->success('All selected products added to cart successfully!');
        } elseif ($addedCount > 0 && count($skippedMessages) > 0) {
            flash()->warning("Some products could not be added due to limited stock:\n" . implode("\n", $skippedMessages));
            flash()->success("{$addedCount} product(s) added to cart.");
        } else {
            flash()->error("No products added. All selected items exceeded available stock:\n" . implode("\n", $skippedMessages));
        }
    }

    public function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::latest()->first();
        $number = $lastInvoice ? (int) str_replace($this->invoice_prefix, '', $lastInvoice->invoice_number) + 1 : 1;
        return $this->invoice_prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function resetForm()
    {
        $this->resetExcept('invoice_prefix', 'currentStep');

        $this->customer_id = null;
        $this->cart = [];
        $this->subtotal = 0;
        $this->total_discount = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->invoice_status = 'unpaid';
        $this->payment_terms = '';
        $this->payment_method = '';
        $this->due_date = now()->addDays(7)->format('Y-m-d');
        $this->invoice_date = now()->format('Y-m-d');
        $this->notes = '';
        $this->assigned_agent = null;
        $this->invoice = null;
        $this->showPrintPreview = false;
        $this->isLoading = false;
    }

    public function submitSalesOrder()
    {
        $this->isLoading = true;

        $validated = $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'assigned_agent' => 'nullable|integer',
            'payment_terms' => 'required|string',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,paypal,other',
            'discount' => 'nullable|numeric|min:0|max:1000000',
            'tax' => 'nullable|numeric|min:0|max:1000000',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'notes' => 'nullable|string|max:1000',
            'terms_conditions' => 'nullable|string|max:2000',
            'cart' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $salesOrder = \App\Models\SalesOrder::create([
                'order_number' => $this->generateSalesOrderNumber(),
                'customer_id' => $this->customer_id,
                'order_date' => $this->order_date,
                'requested_delivery_date' => $this->requested_delivery_date ?? null,
                'status' => 'quotation',
                'payment_terms' => $this->payment_terms,
                'payment_method' => $this->payment_method,
                'discount' => $this->total_discount,
                'tax' => $this->tax,
                'tax_rate' => $this->tax_rate,
                'subtotal' => $this->subtotal,
                'grand_total' => $this->total,
                'notes' => $this->notes,
                'terms_conditions' => $this->terms_conditions,
                'agent_id' => $this->assigned_agent ?: null, // Convert empty string to null
            ]);

            foreach ($this->cart as $item) {
                \App\Models\SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['id'],
                    'name' => $item['name'],
                    'strength' => $item['strength'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'type' => $item['type'] ?? '',
                    'code' => $item['code'] ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'vat' => $item['vat_tax'] ?? 0,
                    'tax_rate' => $item['is_vatable'] ? 12 : 0,
                    'total' => $item['total'],
                ]);
            }

            DB::commit();

            $this->dispatch('sales-order-created');
            $this->resetForm();
            flash()->success('Sales Order created successfully!');
            return redirect()->route('sales-orders');
        } catch (\Exception $e) {
            DB::rollBack();
            flash()->error('Error creating sales order: ' . $e->getMessage());
            logger()->error('Sales order creation error: ' . $e->getMessage());
            return null;
        } finally {
            $this->isLoading = false;
        }
    }

    public function generateSalesOrderNumber()
    {
        $lastOrder = \App\Models\SalesOrder::latest('id')->first();
        $number = $lastOrder ? (int) preg_replace('/\D/', '', $lastOrder->order_number) + 1 : 1;
        $prefix = config('sales_orders.prefix', 'SO');
        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function submitInvoice()
    {
        $this->isLoading = true;

        $validated = $this->validate([
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,paypal,other',
            'due_date' => 'required|date|after_or_equal:today',
            'invoice_date' => 'required|date',
            'assigned_agent' => 'nullable',
            'payment_terms' => 'required|string',
            'discount' => 'nullable|numeric|min:0|max:1000000',
            'tax' => 'nullable|numeric|min:0|max:1000000',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'notes' => 'nullable|string|max:1000',
            'invoice_status' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $this->customer_id,
                'total_amount' => $this->subtotal,
                'discount' => $this->total_discount,
                'tax' => $this->tax,
                'grand_total' => $this->total,
                'status' => $this->invoice_status,
                'payment_terms' => $this->payment_terms,
                'payment_method' => $this->payment_method,
                'due_date' => $this->due_date,
                'issued_date' => $this->invoice_date,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
                'agent_id' => $this->assigned_agent ?? null,
            ]);

            foreach ($this->cart as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'stock_id' => $item['stock_id'] ?? null,
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'total' => $item['total'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['vat_tax'] ?? 0,
                ]);

                if (isset($item['stock_id'])) {
                    $stock = Stock::find($item['stock_id']);
                    if ($stock) {
                        $stock->decrement('quantity', $item['quantity']);
                    }
                }

                $product = Product::find($item['id']);
                if ($product && $product->track_stock) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // If agent_id is set, create agent commission
            if (!empty($this->assigned_agent)) {
                \App\Models\AgentCommission::create([
                    'agent_id' => $this->assigned_agent,
                    'invoice_id' => $invoice->id,
                    'commission_amount' => $invoice->grand_total * 0.05, // Example: 5% commission
                    'status' => 'pending',
                    'notes' => 'Auto-generated commission for invoice #' . $invoice->invoice_number,
                ]);
            }

            // --- Journal Entry Creation ---
            $journalEntry = JournalEntry::create([
                'journal_no' => 'JE-' . now()->format('YmdHis'),
                'journal_date' => now(),
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'description' => 'Invoice #' . $invoice->invoice_number . ' for Customer ' . $invoice->customer->name,
                'status' => 'posted',
            ]);
            $accounts = ChartOfAccount::whereIn('code', [1100, 4000, 2100])->pluck('id', 'code');

            $journalEntry->lines()->create([
                'account_id' => $accounts[1100],
                'debit' => $invoice->grand_total,
                'credit' => 0,
                'memo' => 'Accounts Receivable for Invoice #' . $invoice->invoice_number,
            ]);

            $journalEntry->lines()->create([
                'account_id' => $accounts[4000],
                'debit' => 0,
                'credit' => $invoice->total_amount,
                'memo' => 'Sales Revenue for Invoice #' . $invoice->invoice_number,
            ]);

            if ($invoice->tax > 0) {
                $journalEntry->lines()->create([
                    'account_id' => $accounts[2100],
                    'debit' => 0,
                    'credit' => $invoice->tax,
                    'memo' => 'VAT Payable for Invoice #' . $invoice->invoice_number,
                ]);
            }

            // --- End Journal Entry Creation ---

            DB::commit();

            $this->invoice = $invoice;

            $this->dispatch('invoice-created');

            if (!$this->showPrintPreview) {
                $this->resetForm();
                $this->due_date = now()->addDays(7)->format('Y-m-d');
                $this->invoice_date = now()->format('Y-m-d');
                flash()->success('Invoice created successfully!');
                return redirect()->route('invoicing');
            } else {
                flash()->success('Invoice created successfully!');
                return $invoice;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->invoice = null;
            $this->showPrintPreview = false;
            flash()->error('Error creating invoice: ' . $e->getMessage());
            logger()->error('Invoice creation error: ' . $e->getMessage());
            return null;
        } finally {
            $this->isLoading = false;
        }
    }

    public function print()
    {
        try {
            $this->isLoading = true;
            $this->showPrintPreview = true;

            $createdInvoice = $this->submitInvoice();

            if ($createdInvoice) {
                $this->invoice = Invoice::with(['customer', 'agent', 'items'])->find($createdInvoice->id);

                if ($this->invoice) {
                    usleep(100000);
                    $this->dispatch('open-print-dialog');
                    $this->dispatch('start-pdf-loading');
                } else {
                    $this->showPrintPreview = false;
                    flash()->error('Invoice was created but could not be loaded for printing.');
                }
            } else {
                $this->showPrintPreview = false;
                flash()->error('Failed to Create Sales Order. Please check your data and try again.');
            }
        } catch (\Exception $e) {
            $this->showPrintPreview = false;
            throw $e;
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedSearchProduct()
    {
        $this->loadProducts();
    }

    public function downloadPDF()
    {
        if (!$this->invoice) {
            return;
        }

        $pdf = PDF::loadView('livewire.invoicing.pdf', [
            'invoice' => $this->invoice->load(['customer', 'agent', 'items']),
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'SANVEN-' . $this->invoice->invoice_number . '.pdf');
    }

    public function streamPDF()
    {
        $this->loadingPDFPreview = true;
        $this->dispatch('pdf-loading-started');

        if (!$this->invoice) {
            $this->invoice = $this->submitInvoice();
        }

        $pdf = PDF::loadView('livewire.invoicing.pdf', [
            'invoicing' => $this->invoice->load(['customer', 'agent', 'items']),
        ]);

        $this->loadingPDFPreview = false;
        $this->dispatch('pdf-generation-complete');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->stream();
            },
            'invoice-preview-' . $this->invoice->invoice_number . '.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice-preview-' . $this->invoice->invoice_number . '.pdf"',
            ],
        );
    }

    public function closePrintPreview()
    {
        $this->showPrintPreview = false;
        $this->resetForm();
        $this->currentStep = 1;
    }
}; ?>

<div>
    <div>
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                Create Sales Order
            </h3>
        </div>

        <div class="bg-white dark:bg-(--color-accent-dark) p-8 sm:p-10">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                <div class="md:col-span-1">
                    <div class="space-y-2">
                        <div @class([
                            'flex items-center gap-2 p-3 rounded-lg transition-colors',
                            'bg-(--color-accent-muted) dark:bg-(--color-accent-3-dark) text-(--color-accent) dark:text-(--color-accent-1-dark)' =>
                                $currentStep === 1,
                            'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' =>
                                $currentStep !== 1,
                        ])>
                            <div @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                                'bg-(--color-accent) text-white dark:text-(--color-accent-3-dark)' =>
                                    $currentStep === 1,
                                'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' =>
                                    $currentStep !== 1,
                            ])>1</div>
                            <div>
                                <div class="font-bold">Customer</div>
                                <div class="text-xs">Information</div>
                            </div>
                        </div>

                        <div @class([
                            'flex items-center gap-2 p-3 rounded-lg transition-colors',
                            'bg-(--color-accent-muted) dark:bg-(--color-accent-3-dark) text-(--color-accent) dark:text-(--color-accent-1-dark)' =>
                                $currentStep === 2,
                            'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' =>
                                $currentStep !== 2,
                        ])>
                            <div @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                                'bg-(--color-accent) text-white dark:text-(--color-accent-3-dark)' =>
                                    $currentStep === 2,
                                'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' =>
                                    $currentStep !== 2,
                            ])>2</div>
                            <div>
                                <div class="font-bold">Products</div>
                                <div class="text-xs">Selection</div>
                            </div>
                        </div>

                        <div @class([
                            'flex items-center gap-2 p-3 rounded-lg transition-colors',
                            'bg-(--color-accent-muted) dark:bg-(--color-accent-3-dark) text-(--color-accent) dark:text-(--color-accent-1-dark)' =>
                                $currentStep === 3,
                            'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' =>
                                $currentStep !== 3,
                        ])>
                            <div @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                                'bg-(--color-accent) text-white dark:text-(--color-accent-3-dark)' =>
                                    $currentStep === 3,
                                'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' =>
                                    $currentStep !== 3,
                            ])>3</div>
                            <div>
                                <div class="font-bold">Review</div>
                                <div class="text-xs">& Finalize</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-4 bg-white dark:bg-(--color-accent-1-dark)">
                    @include('livewire.sales-orders.views.step-1-customer-information')
                    {{-- @include('livewire.invoicing.views.step-2-add-products') --}}

                    @if ($currentStep === 2)
                        <div>
                            <!-- Product Search and Add -->
                            <div class="mb-6">
                                <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                                    Step 3: Review Sales Order
                                </h1>
                                <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    Review all details before creating the sales order
                                </span>
                            </div>
                            <div class="mb-4 flex flex-col md:flex-row md:items-end gap-4">

                                <div class="w-full md:w-2/3">
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search
                                        Product</label>
                                    <input type="text" wire:model.debounce.400ms="searchProduct"
                                        placeholder="Search by name, code, or description"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                </div>
                            </div>

                            <!-- Product List -->
                            <div class="mb-6">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Product</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Code</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Available Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($products as $product)
                                            <tr>
                                                <td class="px-4 py-2">
                                                    {{ $product->name }}
                                                    @if ($product->strength || $product->unit?->name || $product->type?->name)
                                                        <span class="ml-1 text-xs text-gray-500">
                                                            @if ($product->strength)
                                                                {{ $product->strength }}
                                                            @endif
                                                            @if ($product->unit?->name)
                                                                ({{ $product->unit->name }})
                                                            @endif
                                                            @if ($product->type?->name)
                                                                <span class="ml-1">{{ $product->type->name }}</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">{{ $product->product_code }}</td>
                                                <td class="px-4 py-2">₱{{ number_format($product->selling_price, 2) }}
                                                </td>
                                                <td class="px-4 py-2">{{ $product->available_qty }}</td>
                                                <td class="px-4 py-2">
                                                    <flux:tooltip content="Add to Cart" placement="top">
                                                        <flux:button wire:click="addToCart({{ $product->id }})"
                                                            icon="plus" icon:variant="micro" variant="primary"
                                                            color="blue" class="gap-1 cursor-pointer">
                                                            Add
                                                        </flux:button>
                                                    </flux:tooltip>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-2 text-center text-gray-500">No
                                                    products found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Cart Table -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">Selected Products</h4>
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Code</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Product</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Order Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Available Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Backorder</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                VAT (12%)</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Line Total</th>
                                            <th class="px-4 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($cart as $item)
                                            @php
                                                $product = \App\Models\Product::find($item['id']);
                                                $availableQty = $product?->available_qty ?? 0;
                                                $orderQty = $item['quantity'];
                                                $backorder = max(0, $orderQty - $availableQty);
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-2">{{ $item['code'] }}</td>
                                                <td class="px-4 py-2">
                                                    {{ $item['name'] }}
                                                    @if ($item['unit'] || $item['type'])
                                                        <span class="ml-1 text-xs text-gray-500">
                                                            @if ($item['strength'])
                                                                ({{ $item['strength'] }})
                                                            @endif
                                                            @if ($item['unit'])
                                                                ({{ $item['unit'] }})
                                                            @endif
                                                            @if ($item['type'])
                                                                <span class="ml-1">{{ $item['type'] }}</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">
                                                    ₱{{ number_format($item['price'], 2) }}
                                                    @if ($item['is_vatable'])
                                                        <span class="text-xs text-gray-500">(VAT Inclusive)</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" min="1"
                                                        wire:model.lazy="cart.{{ $item['id'] }}.quantity"
                                                        class="w-16 rounded border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm"
                                                        wire:change="updateCartItem({{ $item['id'] }})">
                                                </td>
                                                <td class="px-4 py-2">{{ $availableQty }}</td>
                                                <td class="px-4 py-2">
                                                    @if ($backorder > 0)
                                                        <span
                                                            class="text-red-600 font-semibold">{{ $backorder }}</span>
                                                    @else
                                                        <span class="text-green-600">0</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">
                                                    @if ($item['is_vatable'])
                                                        ₱{{ number_format($item['vat_tax'], 2) }}
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">₱{{ number_format($item['total'], 2) }}</td>
                                                <td class="px-4 py-2">
                                                    <flux:tooltip content="Remove from Cart" placement="top">
                                                        <flux:button wire:click="removeFromCart({{ $item['id'] }})"
                                                            icon="trash" icon:variant="micro" variant="danger"
                                                            color="red" class="gap-1 cursor-pointer">
                                                            Remove
                                                        </flux:button>
                                                    </flux:tooltip>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="px-4 py-2 text-center text-gray-500">No
                                                    products in cart.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex justify-between mt-4">
                                <button type="button" wire:click="backToStep1"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded cursor-pointer hover:bg-gray-300">Back</button>
                                <button type="button" wire:click="goToStep3"
                                    class="px-4 py-2 bg-blue-600 text-white cursor-pointer rounded hover:bg-blue-700">Next</button>
                            </div>
                        </div>
                    @endif


                    @include('livewire.sales-orders.views.step-3-review-invoice', [
                        'isEditing' => isset($invoice),
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
