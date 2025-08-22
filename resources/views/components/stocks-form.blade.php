@props([
    'isEditing' => false,
    'products' => [],
    'suppliers' => [],
    'units' => []
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                {{ $isEditing ? 'Edit Stock Details' : 'Receive Stock' }}
            </h3>
        </div>

        <div class="bg-white dark:bg-gray-900 px-6 py-8 shadow-sm">
            <!-- Basic Information -->
            <div class="mb-8">
                <h1 class="font-bold sm:text-base md:text-lg lg:text-xl mb-4">
                    Stock Information
                </h1>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <flux:select wire:model="product_id" :label="__('Product')" size="md" searchable disabled>
                            <flux:select.option value="">Choose product...</flux:select.option>
                            @foreach ($products as $product)
                                <flux:select.option value="{{ $product->id }}">{{ $product->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model="supplier_id" :label="__('Supplier')" size="md" searchable>
                            <flux:select.option value="">Choose supplier...</flux:select.option>
                            @foreach ($suppliers as $supplier)
                                <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model="unit_id" :label="__('Unit')" size="md" disabled>
                            <flux:select.option value="">Choose unit...</flux:select.option>
                            @foreach ($units as $unit)
                                <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            <!-- Inventory & Pricing -->
            <div class="mb-8">
                <h1 class="font-bold sm:text-base md:text-lg lg:text-xl mb-4">
                    Inventory & Pricing
                </h1>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <flux:input wire:model="quantity" :label="__('Quantity')" type="number" min="1"
                            placeholder="Enter stock quantity"
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
                </div>
            </div>

            <!-- Dates -->
            <div class="mb-8">
                <h1 class="font-bold sm:text-base md:text-lg lg:text-xl mb-4">
                    Dates
                </h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model="manufactured_date" :label="__('Manufactured Date')" type="date"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="expiration_date" :label="__('Expiration Date')" type="date"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                </div>
            </div>

            <!-- Batch & Invoice -->
            <div class="mb-8">
                <h1 class="font-bold sm:text-base md:text-lg lg:text-xl mb-4">
                    Batch & Invoice
                </h1>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <flux:input wire:model="batch_number" :label="__('Batch Number')" type="text"
                            placeholder="Enter batch number"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="invoice_number" :label="__('Invoice Number')" type="text"
                            placeholder="Enter invoice number"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="barcode" :label="__('Barcode')" type="text"
                            placeholder="Enter barcode"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                </div>
                <div class="mt-6">
                    <flux:textarea 
                        wire:model="batch_notes" 
                        label="Batch Notes" 
                        placeholder="Enter notes about this batch..."
                        rows="4"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
                    />
                </div>
            </div>

            <!-- Location & Remarks -->
            <div class="mb-8">
                <h1 class="font-bold sm:text-base md:text-lg lg:text-xl mb-4">
                    Storage & Remarks
                </h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input wire:model="location" :label="__('Location')" type="text"
                            placeholder="Enter location"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                    <div>
                        <flux:input wire:model="stock_location" :label="__('Stock Location')" type="text"
                            placeholder="Enter stock location"
                            class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    </div>
                </div>
                <div class="mt-6">
                    <flux:textarea 
                        wire:model="remarks" 
                        label="Remarks" 
                        placeholder="Enter remarks about this stock..."
                        rows="4"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
                    />
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-gray-50 rounded-b-lg dark:bg-(--color-accent-4-dark) p-8 sm:px-6 sm:flex sm:justify-end sm:space-x-2 space-y-2 sm:space-y-0 flex flex-col sm:flex-row">
            <flux:button class="sm:w-auto" variant="danger" wire:click="cancel">Cancel</flux:button>
            <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit" >
                {{ $isEditing ? 'Update Stock' : 'Save Stock' }}
            </flux:button>
        </div>
    </form>
</div>
