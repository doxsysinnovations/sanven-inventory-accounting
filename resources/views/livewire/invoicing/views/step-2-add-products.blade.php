@if($currentStep === 2)
    <div>
        <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between bg-gradient-to-r from-gray-50 to-gray-100 dark:from-(--color-accent-4-dark) dark:to-gray-800 p-6 flex items-center rounded-lg">
                <div>
                    <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                        Step 2: Add Products
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        Search and add products to build your invoice
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative inline-block">
                        <svg class="w-8 h-8 text-(--color-accent) dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>

                        @if (count($cart) > 0)
                            <span class="absolute -top-1 -right-1.5 bg-(--color-accent) text-white text-[8px] font-bold w-5.5 h-5.5 flex items-center justify-center rounded-full shadow">
                                {{ collect($cart)->sum('quantity') }}
                            </span>
                        @endif
                    </div>
                    
                    @if (count($cart) > 0)
                        <div class="text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Cart Total</div>
                            <div class="font-bold text-lg text-(--color-accent) dark:text-blue-400">
                                ₱ {{ number_format($subtotal, 2) }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($showProductModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75">
                <div class="bg-white dark:bg-[#0a0e1a] rounded-xl shadow-xl w-full max-w-7xl mx-4">
                    <div class="flex flex-col">
                        <div class="px-8 pt-8 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Select Products from Stock
                                </h3>
                                <button wire:click="$set('showProductModal', false)"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mb-6">
                                <x-search-bar-2 
                                    id="modal_search_product"
                                    placeholder="Search by product name or code..."
                                    wireModel="searchProduct"
                                />
                            </div>
                            
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
                                <div class="overflow-x-auto">
                                    @php
                                        $cart = collect($cart);
                                    @endphp
                                    <x-list-table
                                        :headers="['Select', 'Product Name', 'Stock #', 'Batch #', 'Expiry Date', 'Available', 'Price', 'Qty To Add']"
                                        :rows="collect($products)->map(fn($stock) => [
                                            view('livewire.invoicing.views.checkbox-product', ['stock' => $stock]),
                                            $stock->product->name,
                                            $stock->stock_number,
                                            $stock->batch_number,
                                            $stock->expiration_date?->format('Y-m-d') ?? 'N/A',
                                            $stock->quantity - collect($cart)->where('stock_id', $stock->id)->sum('quantity'),
                                            number_format($stock->selling_price ?? $stock->product->selling_price, 2),
                                            view('livewire.invoicing.views.change-quantity-2', compact('stock', 'cart'))->render()
                                        ])"
                                    />
                                </div>
                            </div>
                        </div>

                       <div class="bg-gray-50 dark:bg-(--color-accent-4-dark) p-8 sm:flex sm:flex-row-reverse sm:gap-2 sm:px-6 rounded-b-xl space-y-2 sm:space-y-0">
                            <flux:button
                                wire:click="addSelectedProducts"
                                variant="primary"
                                color="blue"
                                class="w-full sm:w-auto">
                                Add Selected to Cart
                            </flux:button>

                            <flux:button
                                variant="danger"
                                wire:click="$set('showProductModal', false)"
                                class="w-full sm:w-auto">
                                Cancel
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-8 px-6">
            <div>
                <div class="flex flex-col sm:flex-row items-start justify-start gap-2 sm:items-center sm:justify-between mb-4">
                    <div class="flex items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Products</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Browse and select products from your inventory</p>
                        </div>
                    </div>

                    <flux:button wire:click="openProductModal" variant="primary" color="blue" icon="plus">
                        Browse Products
                    </flux:button>
                </div>
            </div>

            @if (count($cart) > 0)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Items</h3>
                                <span class="ml-2 px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                                    {{ collect($cart)->count() }} items
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Total Units: {{ collect($cart)->sum('quantity') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <x-list-table
                            :headers="['Product', 'Stock #', 'Price', 'Qty', 'VAT (12%)', 'Total', 'Actions']"
                            :rows="collect($cart)->map(fn($item, $key) => [
                                view('livewire.invoicing.views.name-code-and-expiry-date', ['item' => $item])->render(),
                                $item['stock_number'] ?? 'N/A',
                                '₱ ' . number_format($item['price'], 2),
                                view('livewire.invoicing.views.change-quantity', compact('item', 'key'))->render(),
                                view('livewire.invoicing.views.vat-tax', ['item' => $item])->render(),
                                '₱ ' . number_format($item['total'], 2),
                                view('livewire.invoicing.views.delete-action', ['key' => $key])->render(),
                                '__model' => null,
                            ])"
                        />
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                {{ collect($cart)->count() }} {{ Str::plural('item', collect($cart)->count()) }} • 
                                {{ collect($cart)->sum('quantity') }} {{ Str::plural('unit', collect($cart)->sum('quantity')) }}
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Subtotal</div>
                                <div class="text-xl font-bold text-(--color-accent) dark:text-blue-400">
                                    ₱ {{ number_format($subtotal, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Internal Notes</h3>
                    </div>
                    
                    <flux:textarea 
                        :label="__('')" 
                        wire:model="notes" 
                        rows="3"
                        placeholder="Add any internal notes or comments about this invoice (optional)..."
                    />
                </div>
            @else
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="py-12 flex flex-col items-center justify-center text-center">
                        <svg class="w-16 h-16 mb-4 text-gray-300 dark:text-gray-600"   aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M4.857 3A1.857 1.857 0 0 0 3 4.857v4.286C3 10.169 3.831 11 4.857 11h4.286A1.857 1.857 0 0 0 11 9.143V4.857A1.857 1.857 0 0 0 9.143 3H4.857Zm10 0A1.857 1.857 0 0 0 13 4.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 21 9.143V4.857A1.857 1.857 0 0 0 19.143 3h-4.286Zm-10 10A1.857 1.857 0 0 0 3 14.857v4.286C3 20.169 3.831 21 4.857 21h4.286A1.857 1.857 0 0 0 11 19.143v-4.286A1.857 1.857 0 0 0 9.143 13H4.857Zm10 0A1.857 1.857 0 0 0 13 14.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 21 19.143v-4.286A1.857 1.857 0 0 0 19.143 13h-4.286Z" clip-rule="evenodd"/>
                        </svg>
                        <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">No products added yet.</h4>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Click "Browse Products" above to start adding items to your invoice.
                        </span>
                    </div>
                </div>
            @endif
        
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700 px-6">
                <flux:button variant="ghost" color="zinc" wire:click="backToStep1" icon="chevron-left">
                        Back to Customer
                </flux:button>
                
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                    <div class="text-sm text-center sm:text-left text-gray-500 dark:text-gray-400">
                       <span>Step 2 of 3 - Add Products</span>
                    </div>
                    
                    @if (count($cart) === 0)
                        <flux:button variant="primary" color="blue" disabled>
                            <span>Add Products First</span>
                        </flux:button>
                    @else
                        <flux:button wire:click="goToStep3" variant="primary" color="blue">
                            <span>Review Invoice</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif