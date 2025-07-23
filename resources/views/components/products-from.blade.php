@props([
    'isEditing' => false,
    'brands' => [],
    'categories' => [],
    'types' => [],
    'units' => [],
    'suppliers' => []
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit Product Details' : 'Create Product' }}
            </h3>
        </div>

        <div class="bg-white dark:bg-gray-900 px-6 py-8 rounded-b-lg shadow-sm">
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    Basic Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model="name" :label="__('Product Name')" type="text" placeholder="Enter product name"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="product_code" :label="__('Product Code')" type="text"
                            placeholder="Enter product code" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    Product Details
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <flux:select wire:model.live="product_type" :label="__('Product Type')" size="md" searchable>
                            <flux:select.option value="">Choose product type...</flux:select.option>
                            @foreach ($types as $type)
                                <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.live="brand" :label="__('Brand')" size="md">
                            <flux:select.option value="">Choose brand...</flux:select.option>
                            @foreach ($brands as $brand)
                                <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.live="category" :label="__('Category')" size="md">
                            <flux:select.option value="">Choose category...</flux:select.option>
                            @foreach ($categories as $category)
                                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.live="unit" :label="__('Unit')" size="md">
                            <flux:select.option value="">Choose unit...</flux:select.option>
                            @foreach ($units as $unit)
                                <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model="supplier" :label="__('Supplier')" size="md">
                            <flux:select.option value="">Choose supplier...</flux:select.option>
                            @foreach ($suppliers as $supplier)
                                <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    Image & Description
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Product Image
                        </label>
                        <div class="relative" wire:ignore>
                            <div class="w-full max-w-sm mx-auto lg:mx-0">
                                <div class="relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                                    <img
                                        x-ref="preview"
                                        class="hidden absolute inset-0 w-full h-full object-contain rounded-lg"
                                        alt="Product Preview"
                                    />
                                    <div
                                        x-ref="placeholder"
                                        class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500"
                                    >
                                        <svg class="w-12 h-12 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm">Click to upload image</p>
                                    </div>
                                    <button x-ref="removeButton"
                                        type="button"
                                        class="hidden absolute top-2 right-2 z-10 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 transition-colors"
                                        x-on:reset-preview.window="
                                            $refs.preview.src = '';
                                            $refs.preview.classList.add('hidden');
                                            $refs.placeholder.classList.remove('hidden');
                                            $refs.removeButton.classList.add('hidden');
                                            $refs.fileInput.value = '';
                                            $wire.set('image', null);
                                        ">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3">
                                <flux:input 
                                    x-ref="fileInput" 
                                    wire:model="image" 
                                    type="file" 
                                    accept="image/*"
                                    class="w-full dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
                                    x-on:change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                $refs.preview.src = e.target.result;
                                                $refs.preview.classList.remove('hidden');
                                                $refs.placeholder.classList.add('hidden');
                                                $refs.removeButton.classList.remove('hidden');
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    " 
                                />
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-1 md:col-span-2">
                        <flux:textarea 
                            wire:model="description" 
                            label="Description" 
                            placeholder="Enter product description..."
                            rows="8"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
                        />
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    Inventory & Pricing
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <flux:input wire:model="quantity" :label="__('Initial Stock')" type="number" min="0"
                            placeholder="Enter initial stock"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="quantity_per_piece" :label="__('Quantity Per Piece')" type="number"
                            placeholder="Enter quantity per piece" min="1"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="low_stock_value" :label="__('Low Stock Alert')" type="number"
                            placeholder="Enter low stock threshold" min="0"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="capital_price" :label="__('Capital Price')" type="number"
                            step="0.01" min="0" placeholder="Enter capital price"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="selling_price" :label="__('Selling Price')" type="number"
                            step="0.01" min="0" placeholder="Enter selling price"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="expiration_date" :label="__('Expiration Date')" type="date"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:justify-end sm:space-x-2 space-y-2 sm:space-y-0 flex flex-col sm:flex-row">
                <flux:button class="sm:w-auto" variant="danger" wire:click="cancel">Cancel</flux:button>
                <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit" >
                    {{ $isEditing ? 'Update' : 'Create' }}
                </flux:button>
            </div>
        </div>
    </form>
</div>