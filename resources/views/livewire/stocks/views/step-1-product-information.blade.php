@if ($currentStep === 1)
    <div>
        <h1 class="font-bold sm:text-base md:text-lg lg:text-xl mb-3">
            Product Information
        </h1>

        <div class="overflow-hidden">
            @if (!$product_code)
                <div
                    class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-(--color-accent-2) bg-(--color-accent-2-muted) dark:bg-yellow-900 dark:text-yellow-300 rounded">
                    <div>
                        <span class="text-sm">
                            No product selected yet. Click the <strong>Search Product</strong> button to select a product.
                        </span>
                    </div>
                    <div class="flex justify-end">
                        <flux:button wire:click="$set('openModal', true)" variant="danger">Select Product</flux:button>
                    </div>
                </div>
            @else
                <div
                    class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-green-800 bg-green-100 dark:bg-yellow-900 dark:text-yellow-300 rounded">
                    <div>
                        <span class="text-sm">
                            You have selected a product. Click the <strong>Select Product</strong> button again to change the product.
                        </span>
                    </div>
                    <div class="flex justify-end">
                        <flux:button variant="primary" color="green" wire:click="$set('openModal', true)">Select Product</flux:button>
                    </div>
                </div>
            @endif
        </div>

        @if ($openModal)
            <div class="mt-6 border border-gray-300 dark:border-gray-600 rounded-md p-4 bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Search Product</h3>
                    <button type="button" wire:click="$set('openModal', false)"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100 text-xl">
                        ✕
                    </button>
                </div>

                <div class="mb-4 relative">
                    <x-search-bar
                        placeholder="Search by product name, code, or scan barcode..."
                    />

                    @if ($search)
                        <button type="button" wire:click="$set('search', '')"
                            class="absolute right-2 top-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                            ✕
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <x-list-table
                        :headers="['Product', 'Name', 'Category', 'Action']"
                        :rows="$products->map(fn($product) => [
                            $product->product_code,
                            $product->name,
                            $product->category->name ?? 'Not available.',
                            view('livewire.stocks.views.select-button', ['productId' => $product->id])->render(),
                            '__model' => $product
                        ])"
                        emptyMessage="No products available."
                    />
                </div>
            </div>
        @endif

        <div class="mt-6">
            <flux:input id="product_code" wire:model="product_code" wire:click="$set('openModal', true)"
                :label="__('Product Code')" type="text" placeholder="Please select a product."
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" readonly />
        </div>

        <div class="mt-3">
            <flux:input id="product_name" wire:model="product_name" wire:click="$set('openModal', true)"
                :label="__('Product Name')" type="text" placeholder="Please select a product."
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" readonly />
        </div>

       <div class="col-span-1 sm:col-span-2 lg:col-span-3 mt-3">
            <flux:textarea disabl :label="__('Product Description')" id="product_description" readonly class="w-full text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded"> {{ !empty($product_description) ? $product_description : 'No description available.' }} </flux:textarea>
        </div>

        <div class="mt-2">
            <label for="brand_name" class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">
                Brand Name
            </label>
            <p id="brand_name"
            class="text-sm rounded border border-gray-300 bg-gray-100 dark:bg-gray-200 dark:text-gray-900 p-2">
                {{ $brand_name ?: 'No brand specified.' }}
            </p>
        </div>

        <div class="mt-3">
            <label for="product_category" class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">
                Product Category
            </label>
            <p id="product_category"
            class="text-sm rounded border border-gray-300 bg-gray-100 dark:bg-gray-200 dark:text-gray-900 p-2">
                {{ $product_category ?: 'No category assigned.' }}
            </p>
        </div>

        <div class="flex justify-end mt-4 overflow-hidden">
            <flux:button variant="primary" color="blue" wire:click="nextStep">
                Next
            </flux:button>
        </div>
    </div>
@endif