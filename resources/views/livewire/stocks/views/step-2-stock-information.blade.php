@if ($currentStep === 2)
    <div>
        <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between bg-gray-50 dark:bg-(--color-accent-4-dark) p-6 flex items-center rounded-lg">
                <div>
                    <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                        Step 2: Stock Information
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        Please fill in the stock details to continue.
                    </span>
                </div>
            </div>
        </div>

        <div class="md:px-6">
            <div class="mt-3 mb-2">
                <flux:select :label="__('Supplier')" id="supplier" wire:model="supplier" placeholder="Select a supplier">
                    <flux:select.option value="">Choose Supplier...</flux:select.option>
                    @foreach ($suppliers as $supplier)
                        <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                    @endforeach
                </flux:select>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div class="mb-2">
                    <flux:input :label="__('Capital Price (₱)')" id="capital_price" wire:model="capital_price" type="number" placeholder="0.00"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded"/>
                    <small class="text-gray-500 dark:text-gray-400">Format: 0.00</small>
                </div>

                <div class="mb-2">
                    <flux:input :label="__('Selling Price (₱)')" id="selling_price" wire:model="selling_price" type="number" placeholder="0.00"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" />
                    <small class="text-gray-500 dark:text-gray-400">Format: 0.00</small>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div>
                    <flux:input :label="__('Quantity')" id="quantity" wire:model="quantity" type="number" placeholder="Enter quantity"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the total quantity of stock.</small>
                </div>

                <div>
                    <flux:select :label="__('Unit of Measurement')" id="unit_id" wire:model="unit_id" placeholder="Select a unit">
                         <flux:select.option value="">Choose Unit...</flux:select.option>
                        @foreach ($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <small class="text-gray-500 dark:text-gray-400">Specify the unit of measurement (e.g., Box,
                        Piece).</small>
                </div>

                <div>
                    <flux:input :label="__('Manufactured Date')" id="manufactured_date" wire:model="manufactured_date" type="date"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the manufacturing date, if
                        applicable.</small>
                </div>

                <div>
                    <flux:input :label="__('Expiry Date')" id="expiry_date" wire:model="expiry_date" type="date"
                        class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded" />
                    <small class="text-gray-500 dark:text-gray-400">Enter the expiry date, if applicable.</small>
                </div>

                <div class="mt-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Invoice Number
                    </h2>

                    <div>
                        <flux:input :label="__('Invoice Number')" id="invoice_number" wire:model="invoice_number" type="text"
                            placeholder="Enter invoice number"
                            class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded"/>
                        <small class="text-gray-500 dark:text-gray-400">Enter the supplier's invoice number for
                            reference.</small>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 mt-8 border-t border-gray-200 dark:border-gray-700 md:px-6">
                <flux:button variant="ghost" color="zinc" wire:click="previousStep" icon="chevron-left">
                        Back to Product Information
                </flux:button>
                
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                    <div class="text-sm text-center sm:text-left text-gray-500 dark:text-gray-400">
                       <span>Step 2 of 3 - Add Stock Information</span>
                    </div>
                    
                    <flux:button wire:click="nextStep" variant="primary" color="blue">
                        <span>Continue to Additional Details</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
@endif