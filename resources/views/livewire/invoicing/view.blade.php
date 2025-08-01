<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Stock;
use App\Models\Agent;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

new class extends Component {
    use WithFileUploads;

    public $products = [];
    public $lastInvoice = null;

    // Step tracking
    public $currentStep = 1;
    public $totalSteps = 3;

    // Step 1: Customer Information
    #[Validate('required')]
    public $customer_id = '';
    public $searchCustomer = '';
    public $showCustomerForm = false;
    #[Validate('required|string|max:255')]
    public $name = '';
    #[Validate('required|email|max:255|unique:customers,email')]
    public $email = '';
    #[Validate('nullable|string|max:20')]
    public $phone = '';
    #[Validate('nullable|string')]
    public $address = '';

    // Step 2: Product Information
    public $searchProduct = '';
    public $cart = [];
    #[Validate('nullable|string|max:1000')]
    public $notes = '';
    public $showBulkAddModal = false;
    public $bulkProducts = '';

    // Step 3: Review & Create
    #[Validate('required|string|in:cash,credit_card,bank_transfer,paypal,other')]
    public $payment_method = 'cash';
    #[Validate('required|date|after_or_equal:today')]
    public $due_date;
    #[Validate('nullable|numeric|min:0|max:1000000')]
    public $discount = 0;
    public $is_vatable = false;
    public $total_vat = 0;
    #[Validate('nullable|numeric|min:0|max:1000000')]
    public $tax = 0;
    #[Validate('nullable|numeric|between:0,100')]
    public $tax_rate = 0;
    public $discount_type = 'fixed'; // 'fixed' or 'percentage'
    public $invoice_prefix = 'INV';
    public $invoice_date;
    public $invoice_status;
    public $terms_conditions = 'Payment due within 7 days. Late payments subject to 1.5% monthly interest.';
    public $payment_terms = '';
    public $assigned_agent = '';

    // UI State
    public $showProductModal = false;
    public $selectedProducts = [];
    public $productQuantities = [];

    public $agents = [];

    public $subtotal = 0;
    public $total = 0;
    public $total_discount = 0;

    public $showPrintPreview = false;
    public $loadingPDFPreview = false;
    public $invoice = null;
    public $selectedInvoice;

    public function mount(Invoice $id)
    {
        $this->agents = Agent::all();
        $this->selectedInvoice = $id;
    }   

    public function print()
    {
        $this->selectedInvoice;
        $this->showPrintPreview = true;
        $this->dispatch('open-print-dialog');
        $this->dispatch('start-pdf-loading');
    }

    public function updatedSearchProduct()
    {
        $this->loadStocks();
    }
    
    public function downloadPDF()
    {
        if (!$this->invoice) {
            return;
        }

        $pdf = PDF::loadView('livewire.invoicing.pdf', [
            'invoice' => $this->invoice->load(['customer', 'agent', 'items']),
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'SANVEN-' . $this->invoice->invoice_number . '.pdf');
    }

    public function streamPDF()
    {
        
        $this->loadingPDFPreview = true;
        $this->dispatch('pdf-loading-started');
        
        if (!$this->invoice) {
            $this->invoice = $this->submitInvoice();
        }

        $pdf = PDF::loadView('livewire.invoicing.pdf', [
            'invoicing' => $this->invoice->load(['customer', 'agent', 'items']),
        ]);

        $this->loadingPDFPreview = false;
        $this->dispatch('pdf-generation-complete'); 

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'invoice-preview-' . $this->invoice->invoice_number . '.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-preview-' . $this->invoice->invoice_number . '.pdf"'
        ]);
    }

    public function closePrintPreview()
    {
        $this->showPrintPreview = false;
    }
}; ?>

<div>
    <x-print-pop-up 
        :document="$selectedInvoice" 
        modelInstance="invoice" 
        editRoute="invoicing.edit"
        streamPdfRoute="invoicing.stream-pdf"
        editLabel="Edit Invoice"
        :documentNumber="$selectedInvoice->invoice_number ?? ''">
        <x-invoice-preview :selectedInvoice="$selectedInvoice" />
    </x-print-pop-up>
</div>