<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Product;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

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

    // Items fields
    public $items = [];
    public $products = [];

    public $customers = [];
    public $agents = [];

     public function mount(Quotation $quotation)
    {
        
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();

        $this->isEditing = true;
        $this->quotation = $quotation;
        $this->quotation_number = $quotation->quotation_number;
        $this->customer_id = $quotation->customer_id;
        $this->agent_id = $quotation->agent_id;
        $this->total_amount = $quotation->total_amount;
        $this->tax = $quotation->tax;
        $this->discount = $quotation->discount;
        $this->notes = $quotation->notes;
        $this->status = $quotation->status;
        $this->valid_until = \Carbon\Carbon::parse($quotation->valid_until)->format('Y-m-d');
        $this->items = $quotation->items
            ->map(function ($item) {
                $product = $item->product; 
        
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'is_vatable' => $product->is_vatable ?? false,
                    'vat_tax' => $item->vat_tax,
                    'total_price' => $item->total_price,
                    'description' => $item->description,
                ];
            })
            ->toArray();
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

        $discount = floatval($this->discount ?? 0);

        $total = $baseTotal - $discount;

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
        } else {
            $quotation = Quotation::create($data);
            foreach ($this->items as $item) {
                unset($item['is_vatable']); 
                $quotation->items()->create($item);
            }
            flash()->success('Quotation created successfully!');
        }

        return redirect()->route('quotations');
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
        if($this->isEditing) {
            return redirect()->route('quotations');
        }
    }
};
?>

<div>    
    <x-quotations-form
        :is-editing="false"
        :customers="$customers"
        :products="$products"
        :agents="$agents"
        :withVAT="isset($product) && $product->is_vatable"
    />
</div>