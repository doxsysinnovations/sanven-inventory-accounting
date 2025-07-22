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
};
?>

<x-quotation-preview :quotation="$quotation" />