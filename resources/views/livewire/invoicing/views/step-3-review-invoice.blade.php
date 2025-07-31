@if($currentStep === 3)
    <div class="flex items-center justify-between mb-6">
        <div class="w-full justify-between bg-gray-50 p-6 flex items-center rounded-lg dark:bg-(--color-accent-4-dark)">
            <div>
                <h1 class="font-bold text-base lg:text-lg text-(--color-accent) dark:text-white">
                    Step 3: Review Invoice
                </h1>
                <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    Review details and submit the invoice.
                </span>
            </div>
            <div>
                Total: <span class="text-blue-600 dark:text-blue-400">Php
                {{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="h-full flex flex-col">
            <h3 class="text-base font-bold mb-3">
                Customer Information
            </h3>

            @php 
                $customer = App\Models\Customer::find($customer_id); 
            @endphp

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md flex-col flex-1">
                <div>
                    <span class="font-medium">
                        {{ $customer->name }}
                    </span>
                </div>

                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $customer->email }}
                    </span>
                </div>
                
                @if ($customer->phone)
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $customer->phone }}
                        </span>
                    </div>
                @endif

                @if ($customer->company_name)
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $customer->company_name }}
                        </span>   
                    </div>
                @endif

                @if ($customer->company_name)
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $customer->company_name }}
                        </span>
                    </div>
                @endif

                @if ($customer->address)
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ $customer->address }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <h3 class="text-base font-bold mb-3">
                Invoice Details
            </h3>
            
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
                    <flux:select wire:model="payment_method" :label="__('Payment Method')" size="md" value="">
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
            <flux:select id="payment_terms" wire:model="payment_terms" :label="__('Payment Terms')" size="md" value="">
                <flux:select.option value="">Choose Payment Terms...</flux:select.option>
                <flux:select.option value="Net 15">Cash</flux:select.option>
                <flux:select.option value="Net 30">Net 15</flux:select.option>
                <flux:select.option value="Net 60">Net 60</flux:select.option>
                <flux:select.option value="Net 90">Net 90</flux:select.option>
            </flux:select>
            <small class="text-gray-500 dark:text-gray-400">Select the payment terms for this invoice.</small>
        </div>

        <div>
            <flux:select
                wire:model="assigned_agent" id="assigned_agent_id" :label="__('Assigned Agent')"
            >
                <flux:select.option value="">Select Agent...</flux:select.option>
            
                @foreach ($agents as $agent)
                    <flux:select.option
                        value="{{ $agent->id }}"
                    >
                        {{ $agent->name }}
                    </flux:select.option>svg xml
                @endforeach
            </flux:select>
            <small class="text-gray-500 dark:text-gray-400">Assign this invoice to an agent or staff member.</small>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
            <flux:select id="invoice_status" wire:model="invoice_status" :label="__('Invoice Status')" size="md" value="">
                <flux:select.option value="">Choose Status...</flux:select.option>
                <flux:select.option value="Pending">Pending</flux:select.option>
                <flux:select.option value="Paid">Paid</flux:select.option>
                <flux:select.option value="Overdue">Overdue</flux:select.option>
            </flux:select>
            <small class="text-gray-500 dark:text-gray-400">Set the status of this invoice.</small>
        </div>
    </div>
    
    <div class="mt-3">
        <h3 class="text-base font-bold mb-3">
            Invoice Items
        </h3>
    </div>

    <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
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
    </div>

    <div class="mb-6">
        <div class="space-y-1">
            <div class="flex justify-between py-1">
                <div>
                    <span class="font-bold text-gray-600 dark:text-gray-300">
                        Subtotal:
                    </span>
                </div>
                <div>
                    <span class="font-medium">
                        ₱ {{ number_format($subtotal, 2) }}
                    </span>
                </div>
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

                <span class="font-medium text-(--color-accent-2)">
                    - ₱ {{ number_format($total_discount, 2) }}
                </span>
            </div>

            <div class="flex justify-between py-1">
                <div>
                    <span class="font-bold text-gray-600 dark:text-gray-300">
                        VAT:
                    </span>
                </div>

                @php
                    $totalVat = collect($cart)->sum('vat_tax');
                @endphp
                <div>
                    <span class="font-medium">
                        ₱ {{ number_format($totalVat, 2) }}
                    </span>
                </div>
            </div>

            <div class="flex justify-between py-4 border-t border-gray-200 dark:border-gray-700 mt-2 font-medium text-lg">
                <span class="font-extrabold">
                    GRAND TOTAL:
                </span>
                <span class="font-extrabold">
                    ₱ {{ number_format($total, 2) }}
                </span>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <flux:textarea :label="__('Terms & Conditions')" wire:model="terms_conditions" rows="3" class="w-full text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-2 rounded"> </flux:textarea>
    </div>

    @if ($notes)
        <div class="mb-6">
            <span class="block text-sm font-medium text-zinc-800 dark:text-gray-300 mb-2">Internal Notes</span>
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-sm">
                {{ $notes }}
            </div>
        </div>
    @endif

    <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
        <flux:button variant="ghost" color="zinc" wire:click="backToStep2">
            Back
        </flux:button>
                        
        <div class="flex gap-2">
            <flux:button variant="primary" color="green" icon="printer" wire:click="print">Create & Print</flux:button>  
            <flux:button variant="primary" color="blue" wire:click="submitInvoice">Create Invoice</flux:button>
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