@if($currentStep === 2)
    <div>
        <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between bg-gray-50 p-6 flex items-center rounded-lg dark:bg-(--color-accent-4-dark)">
                <div>
                    <h1 class="font-bold text-base lg:text-lg text-(--color-accent) dark:text-white">
                        Step 2: Add Products
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        Search and add products to the invoice.
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                       {{ count($cart) }} items | Php {{ number_format($subtotal, 2) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-2 mb-6">
            <flux:button wire:click="openProductModal" variant="primary" color="blue" icon="plus">
                    Add Products
            </flux:button>
        </div>

        @if ($showProductModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500/75">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 w-full max-w-7xl mx-4">
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
                                :rows="($products)->map(fn($stock) => [
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

                    <div class="flex items-center justify-end gap-3">
                        <flux:button variant="danger" wire:click="$set('showProductModal', false)">
                            Cancel
                        </flux:button>
                        <flux:button wire:click="addSelectedProducts" variant="primary" color="blue">
                             Add Selected to Cart
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if (count($cart) > 0)
            <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <x-list-table
                        :headers="['Product', 'Stock #', 'Price', 'Qty', 'Total', 'Actions']"
                        :rows="collect($cart)->map(fn($item, $key) => [
                            view('livewire.invoicing.views.name-code-and-expiry-date', ['item' => $item])->render(),
                            $item['stock_number'] ?? 'N/A',
                            number_format($item['price'], 2),
                            view('livewire.invoicing.views.change-quantity', compact('item', 'key'))->render(),
                            number_format($item['total'], 2),
                            view('livewire.invoicing.views.delete-action', ['key' => $key])->render(),
                            '__model' => null,
                        ])"
                    />
                </div>
            </div>

            <div class="mb-6">
                <flux:textarea 
                    :label="__('Notes (Internal)')" 
                    wire:model="notes" rows="3"
                    class="w-full text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded"
                    placeholder="Any internal notes about this invoice..."> 
                </flux:textarea>
            </div>
        @else
            <div class="pb-5 flex flex-col items-center justify-center text-center">
                <svg class="w-32 h-32 sm:w-42 sm:h-42 mb-2 text-gray-300 dark:text-gray-600"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M4.857 3A1.857 1.857 0 0 0 3 4.857v4.286C3 10.169 3.831 11 4.857 11h4.286A1.857 1.857 0 0 0 11 9.143V4.857A1.857 1.857 0 0 0 9.143 3H4.857Zm10 0A1.857 1.857 0 0 0 13 4.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 21 9.143V4.857A1.857 1.857 0 0 0 19.143 3h-4.286Zm-10 10A1.857 1.857 0 0 0 3 14.857v4.286C3 20.169 3.831 21 4.857 21h4.286A1.857 1.857 0 0 0 11 19.143v-4.286A1.857 1.857 0 0 0 9.143 13H4.857Zm10 0A1.857 1.857 0 0 0 13 14.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 21 19.143v-4.286A1.857 1.857 0 0 0 19.143 13h-4.286Z" clip-rule="evenodd"/>
                </svg>
                <span class="font-bold text-sm text-gray-500 dark:text-gray-400 mb-2">No products added to invoice yet.</span>
            </div>
        @endif

        <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
            <flux:button variant="ghost" color="zinc" wire:click="backToStep1">
                Back
            </flux:button>
            @if (count($cart) === 0)
                <flux:button
                    variant="primary"
                    color="blue"
                    disabled
                >
                    Review Invoice
                </flux:button>
            @else
                <flux:button
                    wire:click="goToStep3"
                    variant="primary"
                    color="blue"
                >
                    Review Invoice
                </flux:button>
            @endif
        </div>
    </div>
@endif