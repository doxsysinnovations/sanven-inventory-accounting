@props([
    'quotation' => null,
    'editRoute' => 'quotations.edit',
    'modelInstance' => ''
])

<div x-data="{ 
        showPreview: @entangle('showPrintPreview'), 
        isLoading: false,
        isIframeLoaded: false
    }">    
    <div class="flex justify-end mb-5 gap-2">
        <flux:button variant="primary" color="blue" icon="printer" wire:click="print">Print/Download</flux:button> 
        <div>
            <a href="{{ route($editRoute, [$modelInstance => $quotation]) }}">
                <flux:button variant="primary" color="green" icon="pencil" wire:click="edit">Edit Quotation</flux:button>                      
            </a>
        </div>
    </div>
    
    {{ $slot }}

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
                    Print Preview - {{ $quotation->quotation_number ?? '' }}
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

            @if ($quotation)
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
                        src="{{ route('quotations.stream-pdf', $quotation->id) }}"
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
