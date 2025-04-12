<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $product;
    public $product_code;
    public $name;
    public $description;
    public $supplier;
    public $capital_price;
    public $selling_price;
    public $expiration_date;
    public $quantity;
    public $product_type_id;
    public $unit_id;
    public $brand_id;
    public $category_id;
    public $quantity_per_piece = 1;
    public $low_stock_value = 10;
    public $image;

    public $productTypes = [];
    public $units = [];
    public $brands = [];
    public $categories = [];
    public $types = [];
    public $suppliers = [];

    public function mount($productId)
    {
        $this->product = \App\Models\Product::with('stocks')->findOrFail($productId);

        $this->product_code = $this->product->product_code;
        $this->name = $this->product->name;
        $this->description = $this->product->description;
        $this->product_type_id = $this->product->product_type_id;
        $this->unit_id = $this->product->unit_id;
        $this->brand_id = $this->product->brand_id;
        $this->category_id = $this->product->category_id;
        $this->quantity_per_piece = $this->product->quantity_per_piece;
        $this->low_stock_value = $this->product->low_stock_value;

        // Fill additional details from first stock (optional)
        $stock = $this->product->stocks->first();
        if ($stock) {
            $this->supplier = $stock->supplier_id;
            $this->capital_price = $stock->capital_price;
            $this->selling_price = $stock->selling_price;
            $this->quantity = $stock->quantity;
            $this->expiration_date = $stock->expiration_date;
        }

        $this->loadDropdownData();
    }

    private function loadDropdownData()
    {
        $this->brands = \App\Models\Brand::orderBy('name')->get();
        $this->categories = \App\Models\Category::orderBy('name')->get();
        $this->units = \App\Models\Unit::orderBy('name')->get();
        $this->types = \App\Models\ProductType::orderBy('name')->get();
        $this->suppliers = \App\Models\Supplier::orderBy('name')->get();
    }

    public function update()
    {
        $validated = $this->validate([
            'product_code' => 'required|unique:products,product_code,' . $this->product->id,
            'name' => 'required',
            'description' => 'nullable',
            'product_type_id' => 'required|exists:product_types,id',
            'unit_id' => 'required|exists:units,id',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'quantity_per_piece' => 'required|integer|min:1',
            'low_stock_value' => 'required|integer|min:0',
        ]);

        $this->product->update(
            array_merge($validated, [
                'capital_price' => $this->capital_price,
                'selling_price' => $this->selling_price,
            ]),
        );

        if ($this->image) {
            $this->product->clearMediaCollection('product-image');
            $this->product->addMedia($this->image)->toMediaCollection('product-image');
        }

        $stock = $this->product->stocks()->firstOrNew([]);
        $stock->fill([
            'stock_number' => $stock->stock_number ?? $this->generateStockNumber(),
            'supplier_id' => $this->supplier,
            'quantity' => $this->quantity,
            'capital_price' => $this->capital_price,
            'selling_price' => $this->selling_price,
            'expiration_date' => $this->expiration_date,
        ]);
        $stock->save();

        flash()->success('Product updated successfully!');

        return redirect()->route('products');
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
                    <flux:select wire:model.live="brand_id" :label="__('Brand')" size="md">
                        <option value="">Choose brand...</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" {{ $brand_id == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="category_id" :label="__('Category')" size="md">
                        <option value="">Choose category...</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"  {{ $category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="product_type_id" :label="__('Product Type')" size="md">
                        <option value="">Choose product type...</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}"  {{ $product_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="unit_id" :label="__('Unit')" size="md">
                        <option value="">Choose unit...</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ $unit_id == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
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
                        <div x-ref="placeholder" x-show="!$refs.preview.src"
                            class="absolute inset-0 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Image Preview -->
                        <img x-ref="preview" x-init="$refs.preview.src = '{{ optional($product)->getFirstMediaUrl('product-image') }}';
                        if ($refs.preview.src) {
                            $refs.preview.classList.remove('hidden');
                            $refs.placeholder.classList.add('hidden');
                            $refs.removeButton.classList.remove('hidden');
                        }"
                            class="hidden absolute inset-0 w-full h-full object-cover rounded-lg" />
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
                    {{-- <div class="mb-4">
                        <flux:select wire:model="supplier" :label="__('Supplier')" size="md">
                            <option value="">Choose supplier...</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"  {{ $supplier == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </flux:select>
                    </div> --}}
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
