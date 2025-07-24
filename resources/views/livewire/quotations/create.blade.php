<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Product;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $quotation;
    public $isEditing = false;

    // Form fields
    public $quotation_number = '';
    public $customer_id = null;
    public $agent_id = null;
    public $total_amount = 0;
    public $total_vat = 0;
    public $tax = null;
    public $discount = 0;
    public $discount_type = '';
    public $total_discount = 0;
    public $notes = '';
    public $status = '';
    public $valid_until = '';
    public $is_vatable = false;
    public $showPrintPreview = false;
    public $loadingPDFPreview = false;

    // Items fields
    public $items = [];
    public $products = [];

    public $customers = [];
    public $agents = [];

    public function mount()
    {
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->addItem();
        $this->generateQuotationNumber();
        $this->product = Product::first();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => null,
            'unit_price' => 0,
            'is_vatable' => null,
            'vat_tax' => 0,
            'total_price' => 0,
            'description' => '',
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }
    
    public function calculateTotal()
    {
        $baseTotal = collect($this->items)->sum('total_price');
        $this->tax = collect($this->items)->sum('vat_tax');

        $discountAmount = $this->computeDiscount();

        $total = $baseTotal - $discountAmount;

        $this->total_amount = floatval(sprintf('%.2f', $total));
    }

    public function rules()
    {
        return [
            'quotation_number' => $this->isEditing ? 'required|string|unique:quotations,quotation_number,' . $this->quotation->id : 'required|string|unique:quotations,quotation_number',
            'customer_id' => 'nullable|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'total_amount' => 'required|numeric|min:0',
            'total_vat' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'valid_until' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'quotation_number.required' => 'Quotation number is required.',
            'quotation_number.unique' => 'This quotation number already exists.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'agent_id.exists' => 'Selected agent does not exist.',
            'total_amount.required' => 'Total amount is required.',
            'total_amount.min' => 'Total amount must be 0 or greater.',
            'total_vat.required' => 'Total VAT is required.',
            'total_vat.min' => 'Total VAT must be 0 or greater.',
            'tax.required' => 'Tax is required.',
            'discount.required' => 'Discount is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: draft, sent, accepted, rejected.',
            'valid_until.required' => 'Valid until date is required.',
            'valid_until.after_or_equal' => 'Valid until date must be today or later.',
            'items.required' => 'At least one item is required.',
            'items.*.product_id.required' => 'Please select a product.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.unit_price.min' => 'Unit price must be 0 or greater.',
            'items.*.total_price.required' => 'Total price is required.',
        ];
    }

    public function generateQuotationNumber()
    {
        $this->quotation_number = 'QUO-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
    }

    public function updatedItems($value, $key)
    {
        $index = explode('.', $key)[0];
        $field = explode('.', $key)[1];

        if ($field === 'product_id') {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = $product->selling_price;
                $this->items[$index]['description'] = $product->description;
                $this->items[$index]['is_vatable'] = $product->is_vatable;
            }
        }

        if (in_array($field, ['quantity', 'unit_price', 'product_id'])) {
            $quantity = floatval($this->items[$index]['quantity'] ?? 1);
            $unitPrice = floatval($this->items[$index]['unit_price'] ?? 0);
            $isVatable = $this->items[$index]['is_vatable'] ?? false;

            $subtotal = $quantity * $unitPrice;

            if ($isVatable) {
                $vat = $subtotal * 0.12;
                $this->items[$index]['vat_tax'] = round($vat, 2);
                $this->items[$index]['total_price'] = round($subtotal + $vat, 2);
            } else {
                $this->items[$index]['vat_tax'] = 0;
                $this->items[$index]['total_price'] = round($subtotal, 2);
            }
        }

        $this->tax = collect($this->items)->sum('vat_tax');
        $this->calculateTotal();
    }

    public function updatedTax(){
        $this->calculateTotal();
    }

    public function updatedDiscount() {
        $this->calculateTotal();
    }

    public function updatedDiscountType()
    {
        $this->discount = 0;
        $this->calculateTotal();
    }

    public function computeDiscount()
    {
        $discount = (float)$this->discount;
        $baseTotal = (float) collect($this->items)->sum('total_price');

        if ($this->discount_type === 'percentage') {
            $this->total_discount = $baseTotal * ($discount / 100);
        } else {
            $this->total_discount = $discount;
        }

        return $this->total_discount;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'quotation_number' => $this->quotation_number,
            'customer_id' => $this->customer_id,
            'agent_id' => $this->agent_id,
            'total_amount' => $this->total_amount,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'notes' => $this->notes,
            'status' => $this->status,
            'valid_until' => $this->valid_until,
        ];

        if ($this->isEditing) {
            $this->quotation->update($data);
            $this->quotation->items()->delete();
            foreach ($this->items as $item) {
                unset($item['is_vatable']);
                $this->quotation->items()->create($item);
            }
            flash()->success('Quotation updated successfully!');
            return redirect()->route('quotations');
        } else {
            $this->quotation = Quotation::create($data);
            foreach ($this->items as $item) {
                unset($item['is_vatable']);
                $this->quotation->items()->create($item);
            }
            flash()->success('Quotation created successfully!');
            $quotation = $this->quotation;
            $this->resetForm();
            return $quotation;
        }
    }

    private function resetForm()
    {
        $this->reset(['quotation_number', 'customer_id', 'agent_id', 'total_amount', 'tax', 'discount', 'notes', 'status', 'valid_until', 'quotation', 'items']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->generateQuotationNumber();
        $this->addItem();
    }

    public function cancel() 
    {
        $this->resetForm();
    }

    public function print()
    {
        $this->quotation = $this->save();
        $this->showPrintPreview = true;
        $this->dispatch('open-print-dialog');
        $this->dispatch('start-pdf-loading');
    }

    public function downloadPDF()
    {
        if (!$this->quotation) {
            return;
        }

        $pdf = PDF::loadView('livewire.quotations.pdf', [
            'quotation' => $this->quotation->load(['customer', 'agent', 'items.product']),
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'SANVEN-' . $this->quotation->quotation_number . '.pdf');
    }

    public function streamPDF()
    {

        $this->loadingPDFPreview = true;
        $this->dispatch('pdf-loading-started');

        if (!$this->quotation) {
            $this->quotation = $this->save();
        }

        $pdf = PDF::loadView('livewire.quotations.pdf', [
            'quotation' => $this->quotation->load(['customer', 'agent', 'items.product']),
        ]);

        $this->loadingPDFPreview = false;
        $this->dispatch('pdf-generation-complete'); 

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'quotation-preview-' . $this->quotation->quotation_number . '.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="quotation-preview-' . $this->quotation->quotation_number . '.pdf"'
        ]);
    }

    public function closePrintPreview()
    {
        $this->showPrintPreview = false;
    }
};
?>

<div x-data="{ 
        showPreview: @entangle('showPrintPreview'), 
        isLoading: false,
        isIframeLoaded: false
    }">    
    <x-quotations-form
        :is-editing="false"
        :customers="$customers"
        :products="$products"
        :agents="$agents"
        :withVAT="isset($product) && $product->is_vatable"
        :discount_type="$discount_type"
    />

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