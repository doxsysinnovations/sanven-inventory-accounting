@if ($showCustomerForm)
    <div class="space-y-4 mb-8 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
        <div class="bg-gray-50 px-4 py-4 flex items-center justify-between rounded-md dark:bg-(--color-accent-4-dark)">
            <div>
                <h3 class="font-bold text-lg text-(--color-accent) dark:text-white">
                    New Customer Details
                </h3>
            </div>
            <div class="text-right">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                        Required fields
                    <span class="text-red-500 ml-1">*</span>
                </div>
            </div>
        </div>

        <div class="py-2 px-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer
                        Name 
                        <span class="text-(--color-accent-2)">*</span>
                    </label>
                    <flux:input wire:model="name" :label="__('')" type="text" 
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                        <span class="text-(--color-accent-2)">*</span>
                    </label>
                    <flux:input wire:model="email" :label="__('')" type="email" 
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <flux:input wire:model="phone" :label="__('Phone')" type="tel" 
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
            </div>

            <div>
                <flux:textarea :label="__('Address')" wire:model="address" rows="2"
                    class="w-full text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded"> 
                </flux:textarea>
            </div>

            <div class="flex justify-between pt-4">
                <flux:button wire:click="$set('showCustomerForm', false)" variant="ghost" color="zinc">
                    Cancel
                </flux:button>
                <flux:button wire:click="addCustomer" variant="primary" color="blue">
                    Save Customer
                </flux:button>
            </div>
        </div>
    </div>
@endif