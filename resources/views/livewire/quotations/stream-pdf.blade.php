<?php

use Livewire\Volt\Component;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

new class extends Component {
    public function mount(Quotation $quotation)
    {
        $this->quotation = $quotation->load(['customer', 'agent', 'items.product']);
    }

    public function stream($to, $content, $replace = false)
    {
        $pdf = Pdf::loadView('livewire.quotations.pdf', [
            'quotation' => $this->quotation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'quotation-' . $this->quotation->quotation_number . '.pdf');
    }
}; ?>