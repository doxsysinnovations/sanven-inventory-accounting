<?php

use Livewire\Volt\Component;
use App\Models\Supplier;
use App\Models\Product;

new class extends Component {
    public $currentStep = 1;

    public $request_type = '';

    public $items = [];
    public $itemInput = [
        'product_name' => '',
        'product_description' => '',
        'quantity' => 1,
        'estimated_cost' => 0,
    ];
    public $productSearch = '';
    public $showProductDropdown = false;

    public $remarks = '';


    // Computed property for product suggestions
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
            $this->itemInput['product_name'] = $product->name;
            $this->itemInput['product_description'] = $product->description;
            $this->productSearch = $product->name;
            $this->showProductDropdown = false;
        }
    }

    public function updatedProductSearch()
    {
        $this->showProductDropdown = true;
    }

    public function addItem()
    {
        if (empty($this->itemInput['product_name'])) {
            session()->flash('error', 'Product name is required.');
            return;
        }
        $this->items[] = $this->itemInput;
        $this->itemInput = [
            'product_name' => '',
            'product_description' => '',
            'quantity' => 1,
            'estimated_cost' => 0,
        ];
        $this->productSearch = '';
        $this->showProductDropdown = false;
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function nextStep()
    {
        if ($this->currentStep === 1) {
            if (empty($this->request_type)) {
                session()->flash('error', 'Please select a request type.');
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
            'request_type' => 'required|in:stock,others',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.estimated_cost' => 'required|numeric|min:0',
        ]);

        $pr = \App\Models\PurchaseRequest::create([
            'pr_number' => 'PR-' . now()->format('Ymd') . '-' . strtoupper(\Str::random(5)),
            'requestor_id' => auth()->id(),
            'request_type' => $this->request_type,
            'status' => 'pending',
            'remarks' => $this->remarks,
        ]);

        foreach ($this->items as $item) {
            $pr->items()->create($item);
        }

        session()->flash('message', 'Purchase Request created!');
        return redirect()->route('purchase-requests');
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
                        <a href="{{ route('purchase-requests') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 md:ml-2">Purchase Requests</a>
                    </div>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
        </nav>
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
                        <div class="font-medium">Request Type</div>
                        <div class="text-xs">Selection</div>
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
                        <div class="text-xs">Add Items</div>
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
                        <div class="text-xs">& Submit</div>
                    </div>
                </div>
            </div>
            @if ($currentStep > 1)
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-sm font-medium mb-2">Request Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Items:</span>
                            <span>{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Total Est. Cost:</span>
                            <span>₱{{ number_format(collect($items)->sum(fn($i) => $i['estimated_cost']), 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="md:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            @if ($currentStep === 1)
                <div>
                    <h2 class="text-lg font-semibold mb-4">Step 1: Choose Request Type</h2>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Request Type</label>
                    <select wire:model="request_type"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        <option value="">Select Type</option>
                        <option value="stock">Stock</option>
                        <option value="others">Others</option>
                    </select>
                    <div class="flex justify-end mt-6">
                        <button type="button"
                            wire:click="nextStep"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            :disabled="!request_type">
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
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"
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
                            </ul>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Name</label>
                            <input wire:model="itemInput.product_name" type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea wire:model="itemInput.product_description" rows="1"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                            <input wire:model="itemInput.quantity" type="number" min="1"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Cost</label>
                            <input wire:model="itemInput.estimated_cost" type="number" min="0" step="0.01"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        </div>
                    </div>
                    <div class="flex justify-end mb-6">
                        <button wire:click="addItem" type="button"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Add Item
                        </button>
                    </div>
                    @if (count($items) > 0)
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Est. Cost</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($items as $idx => $item)
                                            <tr>
                                                <td class="px-4 py-3">{{ $item['product_name'] }}</td>
                                                <td class="px-4 py-3">{{ $item['product_description'] }}</td>
                                                <td class="px-4 py-3">{{ $item['quantity'] }}</td>
                                                <td class="px-4 py-3">₱{{ number_format($item['estimated_cost'], 2) }}</td>
                                                <td class="px-4 py-3">
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
                            Review & Submit
                        </button>
                    </div>
                </div>
            @elseif ($currentStep === 3)
                <div>
                    <h2 class="text-lg font-semibold mb-4">Step 3: Review & Submit</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Request Type</label>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            {{ ucfirst($request_type) }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</label>
                        <textarea wire:model="remarks" rows="2"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Est. Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($items as $item)
                                        <tr>
                                            <td class="px-4 py-3">{{ $item['product_name'] }}</td>
                                            <td class="px-4 py-3">{{ $item['product_description'] }}</td>
                                            <td class="px-4 py-3">{{ $item['quantity'] }}</td>
                                            <td class="px-4 py-3">₱{{ number_format($item['estimated_cost'], 2) }}</td>
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
                            Submit
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </form>

</div>