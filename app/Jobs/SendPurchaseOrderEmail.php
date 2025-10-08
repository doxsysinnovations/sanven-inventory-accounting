<?php
namespace App\Jobs;

use App\Models\PurchaseOrder;
use App\Mail\PurchaseOrderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPurchaseOrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $po;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
    }

    public function handle()
    {
        // Send to supplier's email
        $to = $this->po->supplier->email;

        // Optionally, generate PDF and attach
        // $pdf = \PDF::loadView('pdf.purchase-order', ['po' => $this->po])->output();

        Mail::to($to)->send(new PurchaseOrderMail($this->po
            // , $pdf // Uncomment if you want to attach PDF
        ));
    }
}