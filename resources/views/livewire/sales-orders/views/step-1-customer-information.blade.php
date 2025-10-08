@if ($currentStep === 1)
    <div>
        <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between  dark:bg-(--color-accent-4-dark) flex items-center rounded-lg">
                <div>
                    <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                        Step 1: Customer Information
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        Select an existing customer or add a new one to continue.
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-8 md:px-6">
            <div>
                <div class="flex items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Find Existing Customer</h3>
                </div>

                <div class="mb-4">
                    <x-search-bar-2 
                        placeholder="Search by name, email, or phone number..."
                        wireModel="searchCustomer"
                    />
                </div>
                
                @if ($searchCustomer)
                    <div class="space-y-2 max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        @forelse($this->customers() as $customer)
                            <div wire:key="customer-{{ $customer->id }}"
                                class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0 {{ $customer_id == $customer->id ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-l-green-500' : '' }}"
                                wire:click="$set('customer_id', '{{ $customer->id }}')">
                                
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $customer->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $customer->email }}
                                        </div>
                                        @if ($customer->company_name)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                                {{ $customer->company_name }}
                                            </div>
                                        @endif
                                        @if ($customer->phone)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                {{ $customer->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex-shrink-0 ml-2 sm:ml-0">
                                    @if ($customer_id == $customer->id)
                                        <div class="flex items-center text-green-800 dark:text-green-400">
                                            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-sm font-medium">Selected</span>
                                        </div>
                                    @else
                                        <div class="text-gray-400 dark:text-gray-500">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-8 flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">No customers found.</h4>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    No customers match your search criteria. Try a different search term or add a new customer.
                                </span>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- @if ($customer_id && !$showCustomerForm)
                @php $selectedCustomer = \App\Models\Customer::find($customer_id); @endphp
                <div class="bg-green-50 dark:bg-green-800/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                    <div class="flex items-center justify-center sm:justify-start mb-4">
                        <svg class="w-5 h-5 text-green-800 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Selected Customer</h3>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center sm:items-center gap-4 overflow-clip">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-800 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div>
                                <div class="font-semibold text-base text-green-800 dark:text-green-100">{{ $selectedCustomer->name }}</div>
                                
                                @if($selectedCustomer->email)
                                    <div class="flex items-center text-sm text-green-700 dark:text-green-300 mt-1">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $selectedCustomer->email }}
                                    </div>
                                @endif
                                
                                @if ($selectedCustomer->company_name)
                                    <div class="flex items-center text-sm text-green-700 dark:text-green-300 mt-1">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        {{ $selectedCustomer->company_name }}
                                    </div>
                                @endif
                                
                                @if ($selectedCustomer->phone)
                                    <div class="flex items-center text-sm text-green-700 dark:text-green-300 mt-1">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $selectedCustomer->phone }}
                                    </div>
                                @endif

                                @if ($selectedCustomer->address)
                                    <div class="flex items-start text-sm text-green-700 dark:text-green-300 mt-1">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $selectedCustomer->address }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <flux:button variant="primary" color="green" icon="arrow-path" wire:click="$set('customer_id', '')">
                            Change Customer
                        </flux:button>
                    </div>
                </div>
            @endif --}}
            
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Customer</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Can't find the customer? Create a new one.</p>
                        </div>
                    </div>

                    <button wire:click="$set('showCustomerForm', true); $set('customer_id', ''); $set('search', '')" type="button"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[color:var(--color-accent)] hover:text-[#006499] dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Customer
                    </button>
                </div>
            </div>
        </div>

        <div class="md:px-6">
            @include('livewire.invoicing.views.add-new-customer')
        </div>

       <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700 md:px-6">
            <div class="text-sm text-center sm:text-left text-gray-500 dark:text-gray-400">
                <span> Step 1 of 3 - Customer Selection</span>
            </div>

            @if ($customer_id)
                <flux:button class="cursor-pointer" wire:click="goToStep2" variant="primary" color="blue">
                    <span>Continue to Products</span>
                </flux:button>
            @else
                <flux:button variant="primary" color="blue" disabled>
                    <span>Select Customer First</span>
                </flux:button>
            @endif
        </div>
    </div>
@endif