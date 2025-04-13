<?php

use Livewire\Volt\Component;

new class extends Component {
    use Livewire\WithFileUploads;

    public $product_code;
    public $name;
    public $description;
    public $supplier;
    public $capital_price;
    public $selling_price;
    public $expiration_date;
    public $quantity;
    public $product_type;
    public $unit;
    public $brand;
    public $category;
    public $quantity_per_piece = 1;
    public $low_stock_value = 10;
    public $image;

    public $productTypes = [];
    public $units = [];
    public $brands = [];
    public $categories = [];
    public $types = [];
    public $suppliers = [];

    public function mount()
    {
        $this->productTypes = \App\Models\ProductType::all();
        $this->units = \App\Models\Unit::all();
        $this->brands = \App\Models\Brand::orderBy('name')->get();
        $this->categories = \App\Models\Category::orderBy('name')->get();
        $this->types = \App\Models\ProductType::orderBy('name')->get();
        $this->suppliers = \App\Models\Supplier::orderBy('name')->get();
    }

    private function generateStockNumber()
    {
        $yearPrefix = date('Y');
        $lastProduct = \App\Models\Stock::orderBy('id', 'desc')->first();
        if ($lastProduct) {
            $lastStockNumber = intval(substr($lastProduct->stock_number, -6));
            $newStockNumber = $lastStockNumber + 1;
        } else {
            $newStockNumber = 1; // Start from 1 if no products exist
        }
        return $yearPrefix . str_pad($newStockNumber, 6, '0', STR_PAD_LEFT);
    }

    public function save($createAnother = false)
    {
        $validated = $this->validate([
            'product_code' => 'required|unique:products',
            'name' => 'required',
            'description' => 'nullable',
            'product_type' => 'required|exists:product_types,id',
            'unit' => 'required|exists:units,id',
            'brand' => 'required|exists:brands,id',
            'category' => 'required|exists:categories,id',
            'quantity_per_piece' => 'required|integer|min:1',
            'low_stock_value' => 'required|integer|min:0',
        ]);

        $validated['stock_value'] = $this->quantity;
        $validated['capital_price'] = $this->capital_price;
        $validated['selling_price'] = $this->selling_price;

        $product = \App\Models\Product::create($validated);
        if ($this->image) {
            $product->addMedia($this->image)->toMediaCollection('product-image');
        }

        $product->stocks()->create([
            'stock_number' => $this->generateStockNumber(),
            'supplier_id' => $this->supplier,
            'quantity' => $this->quantity,
            'capital_price' => $this->capital_price,
            'selling_price' => $this->selling_price,
            'expiration_date' => $this->expiration_date,
        ]);

        $this->reset();
        flash()->success('Product created successfully!');

        if (!$createAnother) {
            return redirect()->route('products');
        }
    }
}; ?>

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
                        <a href="{{ route('products') }}"
                            class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 md:ml-2">Products</a>
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
            <flux:button href="{{ route('products') }}" variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
                Back
            </flux:button>
        </nav>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <!-- Previous code remains the same until the buttons section -->

        <div class="p-4">

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <flux:input wire:model="product_code" :label="__('Product Code')" type="text"
                        placeholder="ex 123123" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <flux:input wire:model="name" :label="__('Product Name')" type="text" placeholder="ex Product 1"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
            </div>
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div>
                    <flux:select wire:model.live="brand" :label="__('Brand')" size="md">
                        <flux:select.option value="">Choose brand...</flux:select.option>
                        @foreach ($brands as $brand)
                            <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="category" :label="__('Category')" size="md">
                        <flux:select.option value="">Choose category...</flux:select.option>
                        @foreach ($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="product_type" :label="__('Product Type')" size="md" searchable>
                        <flux:select.option value="">Choose product type...</flux:select.option>
                        @foreach ($types as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="unit" :label="__('Unit')" size="md">
                        <flux:select.option value="">Choose unit...</flux:select.option>
                        @foreach ($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <flux:input wire:model="quantity_per_piece" :label="__('Quantity Per Piece')" type="number"
                        placeholder="ex 1" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <flux:input wire:model="low_stock_value" :label="__('Low Stock Value')" type="number"
                        placeholder="ex 10" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
            </div>
            <div class="grid grid-cols-1 mb-4">
                <flux:textarea wire:model="description" label="Description" placeholder="type description..." />
            </div>
            <div class="grid grid-cols-3 mb-4">
                <div wire:ignore>
                    <!-- File Input -->
                    <flux:input x-ref="fileInput" wire:model="image" type="file" accept="image/*"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
                        x-on:change="
                            const file = $event.target.files[0];
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                $refs.preview.src = e.target.result;
                                $refs.preview.classList.remove('hidden');
                                $refs.placeholder.classList.add('hidden');
                                $refs.removeButton.classList.remove('hidden');
                            };
                            reader.readAsDataURL(file);
                        " />

                    <!-- Image Display with Remove Button -->
                    <div class="mt-2 relative w-32 h-32">
                        <!-- Remove Button in top-right corner -->
                        <button x-ref="removeButton"
                            class="hidden absolute top-0 right-0 z-10 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                            x-on:click="
                                $refs.preview.src = '';
                                $refs.preview.classList.add('hidden');
                                $refs.placeholder.classList.remove('hidden');
                                $refs.removeButton.classList.add('hidden');
                                $refs.fileInput.value = '';
                                $wire.set('image', null);
                            ">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Placeholder Icon -->
                        <div x-ref="placeholder"
                            class="absolute inset-0 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Image Preview -->
                        <img x-ref="preview" class="hidden absolute inset-0 w-full h-full object-cover rounded-lg" />
                    </div>
                </div>

                <div class="col-span-2">
                    <div class="mb-4">
                        <flux:input wire:model="quantity" :label="__('Initial Stock')" type="number" min="0"
                            placeholder="Enter initial stock"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div class="mb-4">
                        <flux:input wire:model="capital_price" :label="__('Capital Price')" type="number"
                            step="0.01" min="0" placeholder="Enter capital price"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div class="mb-4">
                        <flux:input wire:model="selling_price" :label="__('Selling Price')" type="number"
                            step="0.01" min="0" placeholder="Enter selling price"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div class="mb-4">
                        <flux:input wire:model="expiration_date" :label="__('Expiration Date')" type="date"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div class="mb-4">
                        <flux:select wire:model="supplier" :label="__('Supplier')" size="md">
                            <flux:select.option value="">Choose supplier...</flux:select.option>
                            @foreach ($suppliers as $supplier)
                                <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>
            <!-- Navigation Buttons -->
            <div class="mt-6 p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <div class="flex justify-end space-x-4">
                    <flux:button variant="primary" wire:click="save">
                        Save
                    </flux:button>
                    <flux:button wire:click="save(true)" variant="primary"
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Save & Create Another
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
