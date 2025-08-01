<?php

use Livewire\Volt\Component;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;

new class extends Component {
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['customer', 'agent', 'items']);
    }

    public function stream()
    {
        $options = new Options();
        $options->setChroot([public_path()]);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = Pdf::setOptions($options)->loadView('livewire.invoicing.pdf', [
            'invoice' => $this->invoice,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'invoice-' . $this->invoice->invoice_number . '.pdf');
    }
};