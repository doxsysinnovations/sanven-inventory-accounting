@if ($currentStep === 1)
    <div>
        <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between bg-gray-50 p-6 flex items-center rounded-lg dark:bg-(--color-accent-4-dark)">
                <div>
                    <h1 class="font-bold text-base lg:text-lg text-(--color-accent) dark:text-white">
                        Step 1: Customer Information
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        Select an existing customer or add a new one.
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Required fields
                        <span class="text-red-500">*</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">
                Search Customers
            </label>
            <x-search-bar-2 
                placeholder="Search by name, email or phone..."
                wireModel="searchCustomer"
            />
        </div>

        @if ($searchCustomer)
            <div class="space-y-2 mb-6 max-h-96 overflow-y-auto">
                @forelse($this->customers() as $customer)
                    <div wire:key="customer-{{ $customer->id }}"
                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        wire:click="$set('customer_id', '{{ $customer->id }}')">
                        <div>
                            <div class="font-medium">{{ $customer->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}</div>
                            @if ($customer->company_name)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $customer->company_name }}</div>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            @if ($customer_id == $customer->id)
                                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-3 flex flex-col items-center justify-center text-center">
                        <svg class="w-32 h-32 sm:w-42 sm:h-42 mb-2 text-gray-300 dark:text-gray-600"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold text-sm text-gray-500 dark:text-gray-400">No customers found matching your search.</span>
                    </div>
                @endforelse
            </div>
        @endif

        <div class="flex items-center justify-between mb-6">
            <button wire:click="$set('showCustomerForm', true)" type="button"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[color:var(--color-accent)] hover:text-[#006499] dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Customer
            </button>

            @if ($customer_id)
                <flux:button wire:click="goToStep2" variant="primary" color="blue">
                        Continue to Products
                </flux:button>
            @endif
        </div>

        @include('livewire.invoicing.views.add-new-customer')

        @if ($customer_id && !$showCustomerForm)
            @php $selectedCustomer = \App\Models\Customer::find($customer_id); @endphp
            <div
                class="mb-6 p-4 bg-green-100 dark:bg-green-900 rounded-md">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $selectedCustomer->name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $selectedCustomer->email }}
                        </div>
                        @if ($selectedCustomer->company_name)
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $selectedCustomer->company_name }}</div>
                        @endif
                        @if ($selectedCustomer->phone)
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $selectedCustomer->phone }}</div>
                        @endif
                    </div>
                    <flux:button variant="primary" color="green" wire:click="$set('customer_id', '')">Change</flux:button>
                </div>
            </div>
        @endif
    </div>
@endif