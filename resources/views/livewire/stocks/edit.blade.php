<?php

use Livewire\Volt\Component;
use App\Models\Stock;

new class extends Component {
    public $stock;

    public $product_id;
    public $supplier_id;
    public $quantity;
    public $capital_price;
    public $selling_price;
    public $expiration_date;
    public $manufactured_date;
    public $batch_number;
    public $batch_notes;
    public $location;
    public $stock_location;
    public $invoice_number;
    public $barcode;
    public $remarks;
    public $unit_id;

    public $products = [];
    public $suppliers = [];
    public $units = [];

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'quantity' => 'required|integer|min:1',
        'capital_price' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'expiration_date' => 'nullable|date',
        'manufactured_date' => 'nullable|date',
        'unit_id' => 'nullable|exists:units,id',
    ];

    public function mount($id)
    {
        $this->stock = Stock::findOrFail($id);

        $this->product_id       = $this->stock->product_id;
        $this->supplier_id      = $this->stock->supplier_id;
        $this->quantity         = $this->stock->quantity;
        $this->capital_price    = $this->stock->capital_price;
        $this->selling_price    = $this->stock->selling_price;
        $this->expiration_date  = optional($this->stock->expiration_date)->format('Y-m-d');
        $this->manufactured_date = optional($this->stock->manufactured_date)->format('Y-m-d');
        $this->batch_number     = $this->stock->batch_number;
        $this->batch_notes      = $this->stock->batch_notes;
        $this->location         = $this->stock->location;
        $this->stock_location   = $this->stock->stock_location;
        $this->invoice_number   = $this->stock->invoice_number;
        $this->barcode          = $this->stock->barcode;
        $this->remarks          = $this->stock->remarks;
        $this->unit_id          = $this->stock->unit_id;

        $this->loadDropdownData();
    }

    private function loadDropdownData()
    {
        $this->products = \App\Models\Product::orderBy('name')->get();
        $this->suppliers = \App\Models\Supplier::orderBy('name')->get();
        $this->units = \App\Models\Unit::orderBy('name')->get();
    }

    public function save()
    {
        $this->validate();

        $this->stock->update([
            'product_id'      => $this->product_id,
            'supplier_id'     => $this->supplier_id,
            'quantity'        => $this->quantity,
            'capital_price'   => $this->capital_price,
            'selling_price'   => $this->selling_price,
            'expiration_date' => $this->expiration_date,
            'manufactured_date' => $this->manufactured_date,
            'batch_number'    => $this->batch_number,
            'batch_notes'     => $this->batch_notes,
            'location'        => $this->location,
            'stock_location'  => $this->stock_location,
            'invoice_number'  => $this->invoice_number,
            'barcode'         => $this->barcode,
            'remarks'         => $this->remarks,
            'unit_id'         => $this->unit_id,
        ]);

        flash()->success('Stock updated successfully!');

        return redirect()->route('stocks');
    }

    public function cancel()
    {
        return redirect()->route('stocks');
    }
};
?>

<div>
    <x-stocks-form
        :is-editing="true"
        :products="$products"
        :suppliers="$suppliers"
        :units="$units"
    />
</div>
