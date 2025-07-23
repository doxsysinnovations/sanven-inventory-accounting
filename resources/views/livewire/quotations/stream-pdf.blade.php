<?php

use Livewire\Volt\Component;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;

new class extends Component {
    public Quotation $quotation;

    public function mount(Quotation $quotation)
    {
        $this->quotation = $quotation->load(['customer', 'agent', 'items.product']);
    }

    public function stream($to, $content, $replace = false)
    {
        $options = new Options();
        $options->setChroot([public_path()]);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = Pdf::setOptions($options)->loadView('livewire.quotations.pdf', [
            'quotation' => $this->quotation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'quotation-' . $this->quotation->quotation_number . '.pdf');
    }
};