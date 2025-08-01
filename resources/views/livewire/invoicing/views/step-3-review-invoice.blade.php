@if($currentStep === 3)
    <div class="flex items-center justify-between mb-6">
        <div class="w-full justify-between bg-gradient-to-r from-gray-50 to-gray-100 dark:from-(--color-accent-4-dark) dark:to-gray-800 p-6 flex items-center rounded-lg">
            <div>
                <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                    Step 3: Review Invoice
                </h1>
                <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                    Review all details before creating the invoice
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
                
                <div class="text-right">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Amount</div>
                    <div class="font-bold text-lg text-(--color-accent) dark:text-blue-400">
                        ₱ {{ number_format($total, 2) }}
                    </div>
                </div>        
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer Information</h3>
                </div>

                @php 
                    $customer = App\Models\Customer::find($customer_id); 
                @endphp

                <div class="space-y-3">
                    <div>
                        <span class="text-base font-medium text-gray-900 dark:text-white">
                            {{ $customer->name }}
                        </span>
                    </div>

                    @if($customer->email)
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $customer->email }}
                        </div>
                    @endif
                    
                    @if ($customer->phone)
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $customer->phone }}
                        </div>
                    @endif

                    @if ($customer->company_name)
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ $customer->company_name }}
                        </div>
                    @endif

                    @if ($customer->address)
                        <div class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $customer->address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Details</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-flux::input wire:model="invoice_date" type="date" label="Invoice Date" />
                        </div>
                        <div>
                            <x-flux::input wire:model="due_date" type="date" label="Due Date" />
                        </div>
                    </div>

                    <div>
                        <flux:select wire:model="payment_method" :label="__('Payment Method')" size="md">
                            <flux:select.option value="">Choose Payment Method...</flux:select.option>
                            <flux:select.option value="cash">Cash</flux:select.option>
                            <flux:select.option value="credit_card">Credit Card</flux:select.option>
                            <flux:select.option value="bank_transfer">Bank Transfer</flux:select.option>
                            <flux:select.option value="paypal">PayPal</flux:select.option>
                            <flux:select.option value="other">Other</flux:select.option>
                        </flux:select>
                    </div>
                </div>
            </div>
        </div>

        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Additional Details</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <flux:select id="payment_terms" wire:model="payment_terms" :label="__('Payment Terms')" size="md">
                        <flux:select.option value="">Choose Payment Terms...</flux:select.option>
                        <flux:select.option value="Cash">Cash</flux:select.option>
                        <flux:select.option value="Net 15">Net 15</flux:select.option>
                        <flux:select.option value="Net 30">Net 30</flux:select.option>
                        <flux:select.option value="Net 60">Net 60</flux:select.option>
                        <flux:select.option value="Net 90">Net 90</flux:select.option>
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model="assigned_agent" id="assigned_agent_id" :label="__('Assigned Agent')">
                        <flux:select.option value="">Select Agent...</flux:select.option>
                        @foreach ($agents as $agent)
                            <flux:select.option value="{{ $agent->id }}">{{ $agent->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:select id="invoice_status" wire:model="invoice_status" :label="__('Invoice Status')" size="md">
                        <flux:select.option value="">Choose Status...</flux:select.option>
                        <flux:select.option value="pending">Pending</flux:select.option>
                        <flux:select.option value="paid">Paid</flux:select.option>
                        <flux:select.option value="overdue">Overdue</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Items</h3>
                    <span class="ml-2 px-2 py-1 text-xs bg-(--color-accent-muted) dark:bg-blue-900 text-(--color-accent) dark:text-blue-300 rounded-full">
                        {{ collect($cart)->count() }} items
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <x-list-table
                    :headers="['Product', 'Price', 'Qty', 'VAT (12%)', 'Total']"
                    :rows="collect($cart)->map(fn($item) => [
                        view('livewire.invoicing.views.name-code-and-expiry-date', ['item' => $item])->render(),
                        'Php ' . number_format($item['price'], 2),
                        $item['quantity'],
                        view('livewire.invoicing.views.vat-tax', ['item' => $item])->render(),
                        'Php ' . number_format($item['total'], 2),
                    ])"
                />
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-600">
                <div class="space-y-1">
                    <div class="flex justify-between items-center py-2">
                        <span class="font-bold text-gray-600 dark:text-gray-300">
                            Subtotal:
                        </span>
                        <span class="text-base font-medium text-gray-900 dark:text-white">₱ {{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="flex justify-between py-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-600 dark:text-gray-300">
                                Discount:
                            </span>

                            <flux:select wire:model.live="discount_type" :label="__('')" size="md" value="">
                                <flux:select.option value="">Choose Discount...</flux:select.option>
                                <flux:select.option value="fixed">Fixed</flux:select.option>
                                <flux:select.option value="percentage">Percentage</flux:select.option>
                            </flux:select>

                            <div class="flex items-center gap-1">
                                @if($discount_type === 'fixed')
                                    <flux:input
                                        type="number"
                                        wire:model.live="discount"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        id="tax"
                                        :disabled="!['discount_type']"
                                    >
                                        <x-slot name="iconLeading">
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                                        </x-slot>
                                    </flux:input>
                                    {{-- <span class="text-sm text-zinc-500 dark:text-zinc-400">(In Pesos)</span> --}}
                                @elseif($discount_type === 'percentage')
                                    <flux:input
                                        type="number"
                                        wire:model.live="discount"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        id="tax"
                                        :disabled="!['discount_type']"
                                    >
                                        <x-slot name="iconTrailing">
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">%</span>
                                        </x-slot>
                                    </flux:input>
                                    {{-- <span class="text-sm text-zinc-500 dark:text-zinc-400">(As Percentage)</span> --}}
                                @else
                                    <flux:input
                                        type="number"
                                        wire:model.live="discount"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        id="tax"
                                        readonly
                                    ></flux:input>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">(%/₱)</span>
                                @endif
                            </div>
                        </div>

                        <span class="font-medium text-(--color-accent-2) items-center">
                            - ₱ {{ number_format($total_discount, 2) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="font-bold text-gray-600 dark:text-gray-300">
                            VAT:
                        </span>
                        @php $totalVat = collect($cart)->sum('vat_tax'); @endphp
                        <span class= "font-medium text-gray-900 dark:text-white">₱ {{ number_format($totalVat, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 dark:border-gray-600">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">GRAND TOTAL:</span>
                        <span class="text-xl font-bold text-(--color-accent) dark:text-blue-400">₱ {{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <flux:textarea :label="__('Terms & Conditions')" wire:model="terms_conditions" rows="3" class="w-full text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded"> </flux:textarea>
            
            @if ($notes)
                <div>
                    <span class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">Internal Notes</span>
                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-sm">
                        {{ $notes }}
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700 px-6">
            <flux:button variant="ghost" color="zinc" wire:click="backToStep2" icon="chevron-left">
                Back to Products
            </flux:button>
                            
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <flux:button 
                    variant="primary" 
                    color="green" 
                    icon="printer" 
                    wire:click="{{ $isEditing ? 'updateAndPrint' : 'print' }}">
                    {{ $isEditing ? 'Update & Print' : 'Create & Print' }}
                </flux:button>

                <flux:button 
                    variant="primary" 
                    color="blue" 
                    wire:click="{{ $isEditing ? 'updateInvoice' : 'submitInvoice' }}">
                    {{ $isEditing ? 'Update Invoice' : 'Create Invoice' }}
                </flux:button>
            </div>
        </div>
    </div>

    <div x-data="{ 
        showPreview: @entangle('showPrintPreview'), 
        isLoading: false,
        isIframeLoaded: false
    }">    
       
        <div x-show="showPreview" 
            x-cloak
            class="fixed inset-0 z-50 flex"
            @open-print-dialog.window="showPreview = true"
            @pdf-loading-started.window="isLoading = true"
            @pdf-generation-complete.window="isLoading = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            
            <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>

            <div class="relative z-50 my-8 mx-auto p-5 w-11/12 max-w-6xl shadow-lg rounded-md bg-white max-h-[95vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Print Preview - {{ $invoice->invoice_number ?? '' }}
                    </h3>
                    <button wire:click="closePrintPreview" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4 flex space-x-2">
                    <flux:button variant="primary" color="blue" icon="printer" onclick="printIframe()">Print</flux:button>
                    <flux:button variant="primary" color="green" icon="document" wire:click="downloadPDF">Download PDF</flux:button>
                </div>

                @if ($invoice)
                    <div x-show="isLoading" class="flex items-center justify-center h-64 text-gray-600">
                        <svg class="animate-spin h-6 w-6 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Generating PDF preview...
                    </div>

                    <div x-show="!isLoading" class="h-[100vh] border border-gray-300 rounded-lg overflow-hidden">
                        <iframe 
                            id="pdfPreview"
                            src="{{ route('invoicing.stream-pdf', $invoice->id) }}"
                            width="100%" 
                            height="100%"
                            class="w-full h-full"
                            frameborder="0"
                            @load="isIframeLoaded = true"
                            x-init="isIframeLoaded = false"
                            x-show="isIframeLoaded"
                        >
                            Your browser does not support PDFs. Please download the PDF to view it.
                        </iframe>
                        
                        <div x-show="!isIframeLoaded && !isLoading" class="flex items-center justify-center h-full text-gray-600">
                            <svg class="animate-spin h-6 w-6 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Loading PDF preview...
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <script>
            function printIframe() {
                const iframe = document.getElementById('pdfPreview');
                iframe.contentWindow.print();
            }
        </script>
    </div>
@endif