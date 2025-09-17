<?php

use Livewire\Volt\Component;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;

new class extends Component {
    public PurchaseOrder $po;

    public function mount(PurchaseOrder $po)
    {
        $this->po = $po->load(['supplier', 'items.product']);
    }

    public function stream($to, $content, $replace = false)
    {
        $options = new Options();
        $options->setChroot([public_path()]);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = Pdf::setOptions($options)->loadView('livewire.purchase-orders.pdf', [
            'po' => $this->po,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'purchase-order-' . $this->po->po_number . '.pdf');
    }
};