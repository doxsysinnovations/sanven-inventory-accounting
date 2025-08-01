<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Product;

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
    public $discount = null;
    public $notes = '';
    public $status = '';
    public $valid_until = '';
    public $is_vatable = false;
    public $quotationInfo;
    public $showPrintPreview = false;
    public $loadingPDFPreview = false;
    
    // Items fields
    public $items = [];
    public $products = [];

    public $customers = [];
    public $agents = [];
    
    public function mount(Quotation $id)
    {
        
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();

        $this->quotation = $id;
    }
    
    public function print()
    {
        $this->quotation;
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
            $this->quotation;
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

<div>
    <x-print-pop-up 
        :document="$quotation"
        modelInstance="quotation" 
        editRoute="quotations.edit"
        streamPdfRoute="quotations.stream-pdf"
        editLabel="Edit Quotation"
        :documentNumber="$quotation->quotation_number ?? ''">
        <x-quotation-preview :quotation="$quotation" />
    </x-print-pop-up>
</div>