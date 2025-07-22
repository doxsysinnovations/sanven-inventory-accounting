<?php

use Livewire\Volt\Component;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

new class extends Component {
    public $po;
    public $currentStep = 1;

    public $order_type = '';
    public $supplier_id = '';
    public $supplierSearch = '';
    public $showSupplierDropdown = false;
    public $showAddSupplierForm = false;
    public $newSupplier = [
        'name' => '',
        'trade_name' => '',
        'contact_number' => '',
        'address' => '',
        'email' => '',
    ];

    public $items = [];
    public $productSearch = '';
    public $showProductDropdown = false;
    public $showAddProductForm = false;
    public $newProduct = [
        'product_code' => '',
        'name' => '',
        'description' => '',
    ];
    public $itemInput = [
        'product_id' => '',
        'quantity' => 1,
        'price' => 0,
    ];
    public $editItemIndex = null;

    public $remarks = '';
    public $payment_terms = '';

    public function mount($id)
    {
        $this->po = PurchaseOrder::with(['items', 'supplier'])->findOrFail($id);
        $this->order_type = $this->po->order_type;
        $this->supplier_id = $this->po->supplier_id;
        $this->supplierSearch = $this->po->supplier->name ?? '';
        $this->remarks = $this->po->remarks;
        $this->payment_terms = $this->po->payment_terms;
        $this->items = $this->po->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();
    }

    // --- Supplier logic ---
    public function getSupplierSuggestionsProperty()
    {
        if (strlen($this->supplierSearch) < 2) return collect();
        return Supplier::where('name', 'like', '%' . $this->supplierSearch . '%')
            ->orWhere('trade_name', 'like', '%' . $this->supplierSearch . '%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectSupplier($id)
    {
        $this->supplier_id = $id;
        $supplier = Supplier::find($id);
        $this->supplierSearch = $supplier ? $supplier->name : '';
        $this->showSupplierDropdown = false;
        $this->showAddSupplierForm = false;
    }

    public function updatedSupplierSearch()
    {
        $this->showSupplierDropdown = true;
    }

    public function showAddSupplier()
    {
        $this->showAddSupplierForm = true;
        $this->showSupplierDropdown = false;
    }

    public function saveSupplier()
    {
        $this->validate([
            'newSupplier.name' => 'required|string|max:255',
            'newSupplier.trade_name' => 'required|string|max:255',
            'newSupplier.contact_number' => 'nullable|string|max:255',
            'newSupplier.address' => 'required|string|max:255',
            'newSupplier.email' => 'nullable|email|max:255',
        ]);
        $supplier = Supplier::create($this->newSupplier);
        $this->supplier_id = $supplier->id;
        $this->supplierSearch = $supplier->name;
        $this->showAddSupplierForm = false;
    }

    // --- Product logic ---
    public function getProductSuggestionsProperty()
    {
        if (strlen($this->productSearch) < 2) return collect();
        return Product::where('name', 'like', '%' . $this->productSearch . '%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->itemInput['product_id'] = $product->id;
            $this->productSearch = $product->name;
            $this->showProductDropdown = false;
            $this->showAddProductForm = false;
        }
    }

    public function updatedProductSearch()
    {
        $this->showProductDropdown = true;
    }

    public function showAddProduct()
    {
        $this->showAddProductForm = true;
        $this->showProductDropdown = false;
    }

    public function saveProduct()
    {
        $this->validate([
            'newProduct.product_code' => 'required|string|max:255|unique:products,product_code',
            'newProduct.name' => 'required|string|max:255',
            'newProduct.description' => 'nullable|string|max:255',
        ]);
        $product = Product::create([
            'product_code' => $this->newProduct['product_code'],
            'name' => $this->newProduct['name'],
            'description' => $this->newProduct['description'],
        ]);
        $this->itemInput['product_id'] = $product->id;
        $this->productSearch = $product->name;
        $this->showAddProductForm = false;
        $this->newProduct = [
            'product_code' => '',
            'name' => '',
            'description' => '',
        ];
    }

    public function addItem()
    {
        if (empty($this->itemInput['product_id'])) {
            session()->flash('error', 'Product is required.');
            return;
        }
        if ($this->editItemIndex !== null) {
            $this->items[$this->editItemIndex]['product_id'] = $this->itemInput['product_id'];
            $this->items[$this->editItemIndex]['quantity'] = $this->itemInput['quantity'];
            $this->items[$this->editItemIndex]['price'] = $this->itemInput['price'];
            if (isset($this->items[$this->editItemIndex]['id'])) {
                \App\Models\PurchaseOrderItem::where('id', $this->items[$this->editItemIndex]['id'])
                    ->update([
                        'product_id' => $this->itemInput['product_id'],
                        'quantity' => $this->itemInput['quantity'],
                        'price' => $this->itemInput['price'],
                    ]);
            }
            $this->editItemIndex = null;
        } else {
            $this->items[] = [
                'product_id' => $this->itemInput['product_id'],
                'quantity' => $this->itemInput['quantity'],
                'price' => $this->itemInput['price'],
            ];
        }
        $this->itemInput = [
            'product_id' => '',
            'quantity' => 1,
            'price' => 0,
        ];
        $this->productSearch = '';
        $this->showProductDropdown = false;
    }

    public function editItem($index)
    {
        $item = $this->items[$index];
        $this->itemInput = [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
        ];
        $product = Product::find($item['product_id']);
        $this->productSearch = $product ? $product->name : '';
        $this->editItemIndex = $index;
        $this->showProductDropdown = false;
        $this->showAddProductForm = false;
    }

    public function removeItem($index)
    {
        $item = $this->items[$index];
        if (isset($item['id'])) {
            \App\Models\PurchaseOrderItem::find($item['id'])?->delete();
        }
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if ($this->editItemIndex === $index) {
            $this->editItemIndex = null;
            $this->itemInput = [
                'product_id' => '',
                'quantity' => 1,
                'price' => 0,
            ];
            $this->productSearch = '';
        }
    }

    public function nextStep()
    {
        if ($this->currentStep === 1) {
            if (empty($this->order_type) || empty($this->supplier_id)) {
                session()->flash('error', 'Please select an order type and supplier.');
                return;
            }
        }
        if ($this->currentStep === 2) {
            if (count($this->items) === 0) {
                session()->flash('error', 'Please add at least one product.');
                return;
            }
        }
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function submit()
    {
        $this->validate([
            'order_type' => 'required|in:stock,others',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_terms' => 'required|in:net 15,net 30,net 60,50% downpayment,installments,upon delivery',
        ]);

        $this->po->update([
            'order_type' => $this->order_type,
            'supplier_id' => $this->supplier_id,
            'remarks' => $this->remarks,
            'payment_terms' => $this->payment_terms,
        ]);

        // Only create new items for those without id
        foreach ($this->items as $item) {
            if (!isset($item['id'])) {
                $this->po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }

        session()->flash('message', 'Purchase Order updated!');
        return redirect()->route('purchase-orders');
    }
}; ?>

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
                        <a href="{{ route('purchase-orders') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 md:ml-2">Purchase Orders</a>
                    </div>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 md:ml-2">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Edit Purchase Order</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Update the purchase order details or modify items as needed.
            </p>
        </div>
    </div>

    <form wire:submit.prevent="submit">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Stepper Sidebar -->
        <div class="md:col-span-1">
            <div class="space-y-2">
                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg transition-colors',
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' => $currentStep === 1,
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' => $currentStep !== 1,
                ])>
                    <div @class([
                        'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                        'bg-blue-600 text-white' => $currentStep === 1,
                        'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' => $currentStep !== 1,
                    ])>1</div>
                    <div>
                        <div class="font-medium">Order Type & Supplier</div>
                        <div class="text-xs">Edit Selection</div>
                    </div>
                </div>
                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg transition-colors',
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' => $currentStep === 2,
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' => $currentStep !== 2,
                ])>
                    <div @class([
                        'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                        'bg-blue-600 text-white' => $currentStep === 2,
                        'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' => $currentStep !== 2,
                    ])>2</div>
                    <div>
                        <div class="font-medium">Products</div>
                        <div class="text-xs">Add or Edit Items</div>
                    </div>
                </div>
                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg transition-colors',
                    'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100' => $currentStep === 3,
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' => $currentStep !== 3,
                ])>
                    <div @class([
                        'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                        'bg-blue-600 text-white' => $currentStep === 3,
                        'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' => $currentStep !== 3,
                    ])>3</div>
                    <div>
                        <div class="font-medium">Review</div>
                        <div class="text-xs">& Payment</div>
                    </div>
                </div>
            </div>
            @if ($currentStep > 1)
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-sm font-medium mb-2">Order Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Items:</span>
                            <span>{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Total Cost:</span>
                            <span>₱{{ number_format(collect($items)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="md:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            @if ($currentStep === 1)
                <div>
                    <h2 class="text-lg font-semibold mb-4">Step 1: Choose Order Type & Supplier</h2>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order Type</label>
                    <select wire:model="order_type"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        <option value="">Select Type</option>
                        <option value="stock">Stock</option>
                        <option value="others">Others</option>
                    </select>

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 mt-4">Supplier</label>
                    <div class="relative mb-4">
                        <input wire:model.live="supplierSearch"
                            type="text"
                            placeholder="Type supplier name..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100"
                            wire:focus="showSupplierDropdown = true"
                            wire:keydown.escape="showSupplierDropdown = false"
                            autocomplete="off"
                        >
                        @if ($showSupplierDropdown && strlen($supplierSearch) > 1)
                            <ul class="absolute z-10 bg-white border rounded shadow mb-2 w-full max-h-48 overflow-auto">
                                @forelse ($this->supplierSuggestions as $supplier)
                                    <li class="px-4 py-2 text-gray-500 hover:bg-blue-100 cursor-pointer"
                                        wire:click.prevent="selectSupplier({{ $supplier->id }})">
                                        {{ $supplier->trade_name }}
                                    </li>
                                @empty
                                    <li class="px-4 py-2 text-gray-500">No suppliers found.</li>
                                @endforelse
                                <li class="px-4 py-2 text-blue-600 hover:bg-blue-50 cursor-pointer"
                                    wire:click.prevent="showAddSupplier">
                                    + Add New Supplier
                                </li>
                            </ul>
                        @endif
                    </div>
                    @if ($showAddSupplierForm)
                        <div class="p-4 border rounded bg-white dark:bg-gray-800 mt-2">
                            <label>Name (Contact Person)</label>
                            <input type="text" wire:model="newSupplier.name" class="w-full mb-2" />
                            <label>Trade Name (Company)</label>
                            <input type="text" wire:model="newSupplier.trade_name" class="w-full mb-2" />
                            <label>Contact Number</label>
                            <input type="text" wire:model="newSupplier.contact_number" class="w-full mb-2" />
                            <label>Address</label>
                            <input type="text" wire:model="newSupplier.address" class="w-full mb-2" />
                            <label>Email</label>
                            <input type="email" wire:model="newSupplier.email" class="w-full mb-2" />
                            <button type="button" wire:click="saveSupplier" class="px-4 py-2 bg-green-600 text-white rounded">Save Supplier</button>
                        </div>
                    @endif
                    <div class="flex justify-end mt-6">
                        <button type="button"
                            wire:click="nextStep"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            :disabled="!order_type || !supplier_id">
                            Continue to Products
                        </button>
                    </div>
                </div>
            @elseif ($currentStep === 2)
                <div>
                    <h2 class="text-lg font-semibold mb-4">Step 2: Add Products</h2>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Product</label>
                    <div class="relative mb-4">
                        <input wire:model.live="productSearch"
                            type="text"
                            placeholder="Type product name..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100"
                            wire:focus="showProductDropdown = true"
                            wire:keydown.escape="showProductDropdown = false"
                            autocomplete="off"
                        >
                        @if ($showProductDropdown && strlen($productSearch) > 1)
                            <ul class="absolute z-10 bg-white border rounded shadow mb-2 w-full max-h-48 overflow-auto">
                                @forelse ($this->productSuggestions as $product)
                                    <li class="px-4 py-2 text-gray-500 hover:bg-blue-100 cursor-pointer"
                                        wire:click.prevent="selectProduct({{ $product->id }})">
                                        {{ $product->name }}
                                    </li>
                                @empty
                                    <li class="px-4 py-2 text-gray-500">No products found.</li>
                                @endforelse
                                <li class="px-4 py-2 text-blue-600 hover:bg-blue-50 cursor-pointer"
                                    wire:click.prevent="showAddProduct">
                                    + Add New Product
                                </li>
                            </ul>
                        @endif
                    </div>
                    @if ($showAddProductForm)
                        <div class="p-4 border rounded bg-white dark:bg-gray-800 mt-2">
                            <label>Product Code</label>
                            <input type="text" wire:model="newProduct.product_code" class="w-full mb-2" /> 
                            <label>Product Name</label>
                            <input type="text" wire:model="newProduct.name" class="w-full mb-2" />
                            <label>Description</label>
                            <textarea wire:model="newProduct.description" class="w-full mb-2"></textarea>
                            <button type="button" wire:click="saveProduct" class="px-4 py-2 bg-green-600 text-white rounded">Save Product</button>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                            <input wire:model="itemInput.quantity" type="number" min="1"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price</label>
                            <input wire:model="itemInput.price" type="number" min="0" step="0.01"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                    <div class="flex justify-end mb-6">
                        <button wire:click="addItem" type="button"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            @if($editItemIndex !== null)
                                Update Item
                            @else
                                Add Item
                            @endif
                        </button>
                        @if($editItemIndex !== null)
                            <button wire:click="$set('editItemIndex', null)" type="button"
                                class="ml-2 px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                                Cancel
                            </button>
                        @endif
                    </div>
                    @if (count($items) > 0)
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($items as $idx => $item)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    {{ \App\Models\Product::find($item['product_id'])->name ?? '' }}
                                                </td>
                                                <td class="px-4 py-3">{{ $item['quantity'] }}</td>
                                                <td class="px-4 py-3">₱{{ number_format($item['price'], 2) }}</td>
                                                <td class="px-4 py-3 flex gap-2">
                                                    <button wire:click="editItem({{ $idx }})" type="button"
                                                        class="text-blue-600 hover:text-blue-900">Edit</button>
                                                    <button wire:click="removeItem({{ $idx }})" type="button"
                                                        class="text-red-600 hover:text-red-900">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="previousStep" type="button"
                            class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                            Back
                        </button>
                        <button wire:click="nextStep" type="button"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors"
                            @if(count($items) === 0) disabled @endif>
                            Review & Payment
                        </button>
                    </div>
                </div>
            @elseif ($currentStep === 3)
                <div>
                    <h2 class="text-lg font-semibold mb-4">Step 3: Review & Payment</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order Type</label>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            {{ ucfirst($order_type) }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</label>
                        <textarea wire:model="remarks" rows="2"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Terms</label>
                        <select wire:model="payment_terms"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                            <option value="">Select Payment Terms</option>
                            <option value="net 15">Net 15</option>
                            <option value="net 30">Net 30</option>
                            <option value="net 60">Net 60</option>
                            <option value="50% downpayment">50% Downpayment</option>
                            <option value="installments">Installments</option>
                            <option value="upon delivery">Upon Delivery</option>
                        </select>
                    </div>
                    <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($items as $item)
                                        <tr>
                                            <td class="px-4 py-3">{{ \App\Models\Product::find($item['product_id'])->name ?? '' }}</td>
                                            <td class="px-4 py-3">{{ $item['quantity'] }}</td>
                                            <td class="px-4 py-3">₱{{ number_format($item['price'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="previousStep" type="button"
                            class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                            Back
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 transition-colors">
                            Update
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </form>
</div>