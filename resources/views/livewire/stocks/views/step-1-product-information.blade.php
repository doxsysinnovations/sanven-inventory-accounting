@if ($currentStep === 1)
    <div>
        <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between bg-gray-50 dark:bg-(--color-accent-4-dark) p-6 flex items-center rounded-lg">
                <div>
                    <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                        Step 1: Product Information
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        Select a product to continue.
                    </span>
                </div>
            </div>
        </div>

        @if ($openModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75">
                <div class="bg-white dark:bg-[#0a0e1a] rounded-xl shadow-xl w-full max-w-7xl mx-4">
                    <div class="flex flex-col max-h-[80vh]">
                        <div class="px-8 pt-8 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Select Products from Stock
                                </h3>
                                <button wire:click="$set('openModal', false)"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mb-6">
                                <div>
                                    <x-search-bar-2
                                        placeholder="Search by product name, code, or scan barcode..."
                                        wireModel="search"
                                    />
                                </div>

                                @if ($search)
                                    <button type="button" wire:click="$set('search', '')"
                                        class="absolute right-2 top-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                                        ✕
                                    </button>
                                @endif
                            </div>
                            
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="overflow-x-auto overflow-y-auto max-h-[500px]">
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
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="px-6">
            <div class="overflow-hidden">
                @if (!$product_code)
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-2 bg-red-50 dark:bg-red-800/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div>
                            <span class="text-sm text-red-300">
                                No product selected yet. Click the <strong>Search Product</strong> button to select a product.
                            </span>
                        </div>
                        <div class="flex justify-end">
                            <flux:button wire:click="$set('openModal', true)" variant="danger">Select Product</flux:button>
                        </div>
                    </div>
                @else
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-2 bg-green-50 dark:bg-green-800/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div>
                            <span class="text-sm text-green-300">
                                You have selected a product. Click the <strong>Select Product</strong> button again to change the product.
                            </span>
                        </div>
                        <div class="flex justify-end">
                            <flux:button variant="primary" color="green" wire:click="$set('openModal', true)">Select Product</flux:button>
                        </div>
                    </div>
                @endif
            </div>

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
                <flux:textarea disable :label="__('Product Description')" id="product_description" readonly class="w-full text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded"> {{ !empty($product_description) ? $product_description : 'No description available.' }} </flux:textarea>
            </div>

            <div class="mt-2">
                <label for="brand_name" class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">
                    Brand Name
                </label>
                <p id="brand_name"
                    class="bg-gray-100 dark:bg-[#353F4D] w-full rounded block disabled:shadow-none dark:shadow-none border appearance-none text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3
                        text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400
                        dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-gray-300 border-b-zinc-300/80 disabled:border-b-zinc-200
                        dark:border-white/10 dark:disabled:border-white/5">
                    {{ $brand_name ?: 'No brand specified.' }}
                </p>
            </div>

            <div class="mt-3">
                <label for="product_category" class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">
                    Product Category
                </label>
                <p id="product_category"
                    class="bg-gray-100 dark:bg-[#353F4D] w-full rounded block disabled:shadow-none dark:shadow-none border appearance-none text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3
                        text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400
                        dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-gray-300 border-b-zinc-300/80 disabled:border-b-zinc-200
                        dark:border-white/10 dark:disabled:border-white/5">
                    {{ $product_category ?: 'No category assigned.' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 mt-8 border-t border-gray-200 dark:border-gray-700 px-6">
                <div class="text-sm text-center sm:text-left text-gray-500 dark:text-gray-400">
                    <span> Step 1 of 3 - Product Selection</span>
                </div>

                @if ($product_id)
                    <flux:button wire:click="nextStep" variant="primary" color="blue">
                        <span>Continue to Stock</span>
                    </flux:button>
                @else
                    <flux:button variant="primary" color="blue" disabled>
                        <span>Select Product First</span>
                    </flux:button>
                @endif
            </div>
        </div>
    </div>
@endif