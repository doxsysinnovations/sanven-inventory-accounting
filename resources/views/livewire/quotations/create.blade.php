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
    public $tax = null;
    public $discount = null;
    public $notes = '';
    public $status = '';
    public $valid_until = '';

    // Items fields
    public $items = [];
    public $products = [];

    public $customers = [];
    public $agents = [];

    public function mount()
    {
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->addItem();
        $this->generateQuotationNumber();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => null,
            'unit_price' => null,
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
        $this->total_amount = collect($this->items)->sum('total_price');
        $this->total_amount = $this->total_amount + $this->total_amount * ($this->tax / 100) - $this->discount;
    }

    public function rules()
    {
        return [
            'quotation_number' => $this->isEditing ? 'required|string|unique:quotations,quotation_number,' . $this->quotation->id : 'required|string|unique:quotations,quotation_number',
            'customer_id' => 'nullable|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'total_amount' => 'required|numeric|min:0',
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

    // public function create()
    // {
    //     $this->resetForm();
    //     $this->isEditing = false;
    //     $this->generateQuotationNumber();
    // }

    public function edit(Quotation $quotation)
    {
        $this->resetValidation();
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
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'description' => $item->description,
                ];
            })
            ->toArray();
        $this->isEditing = true;
    }

    public function updatedItems($value, $key)
    {
        $index = explode('.', $key)[0];
        $field = explode('.', $key)[1];

        if ($field === 'product_id') {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = $product->selling_price; // Changed from price to selling_price
                $this->items[$index]['description'] = $product->description;
                // Auto-calculate total when product changes
                $this->items[$index]['total_price'] = $this->items[$index]['quantity'] * $this->items[$index]['unit_price'];
            }
        }

        if ($field === 'quantity' || $field === 'unit_price') {
            $this->items[$index]['total_price'] = floatval($this->items[$index]['quantity']) * floatval($this->items[$index]['unit_price']);
        }

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
                $this->quotation->items()->create($item);
            }
            flash()->success('Quotation updated successfully!');
        } else {
            $quotation = Quotation::create($data);
            foreach ($this->items as $item) {
                $quotation->items()->create($item);
            }
            flash()->success('Quotation created successfully!');
        }

        $this->resetForm();
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
        $this->resetForm();
    }
};
?>

<div>    
    <div>
        <form wire:submit.prevent="save">
            <div class="bg-gray-50 p-6 flex items-center">
                <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                    {{ $isEditing ? 'Edit Quotation' : 'Create New Quotation' }}
                </h3>
            </div>
            
            <div class="bg-white dark:bg-gray-900 px-6 pt-6 pb-6 sm:p-8 sm:pb-8">
           
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="mb-4">
                        @if($isEditing)
                            <flux:input wire:model="quotation_number" :label="__('Quotation Number')" type="text"
                                readonly class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        @else
                            <flux:input wire:model="quotation_number" :label="__('Quotation Number')" type="text"
                                readonly class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        @endif
                    </div>
                    <div class="mb-4">
                        <flux:select wire:model.live="status" :label="__('Status')" size="md" value="">
                            <flux:select.option value="">Choose Status...</flux:select.option>
                            <flux:select.option value="draft">Draft</flux:select.option>
                            <flux:select.option value="sent">Sent</flux:select.option>
                            <flux:select.option value="accepted">Accepted</flux:select.option>
                            <flux:select.option value="rejected">Rejected</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="mb-4">
                        <flux:select wire:model.live="customer_id" :label="__('Customer')" size="md">
                            <flux:select.option value="">Choose customer...</flux:select.option>
                            @foreach ($customers as $customer)
                                <flux:select.option value="{{ $customer->id }}">{{ $customer->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                    </div>
                </div>
                <div class="mt-4 mb-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Items</h4>
                    <div class="space-y-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="w-2/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Product
                                    </th>
                                    <th
                                        class="w-1/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Quantity
                                    </th>
                                    <th
                                        class="w-1/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Unit Price
                                    </th>
                                    <th
                                        class="w-1/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Total
                                    </th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($items as $index => $item)
                                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
                                    wire:key="item-{{ $index }}">
                                    <td class="w-2/5 px-6 py-4 align-top">
                                        <div class="space-y-1">
                                            <flux:select
                                                wire:model.live.debounce.500ms="items.{{ $index }}.product_id"
                                                name="items.{{ $index }}.product_id"
                                                size="md"
                                                :label="__('')"
                                            >
                                                <flux:select.option value="">Select Product...</flux:select.option>
                                            
                                                @foreach ($products as $product)
                                                    <flux:select.option
                                                        value="{{ $product['id'] }}"
                                                    >
                                                        {{ $product['name'] }}
                                                    </flux:select.option>
                                                @endforeach
                                            </flux:select>
                                        </div>
                                    </td>
                                    <td class="w-1/5 px-6 py-4 align-top">
                                        <div class="space-y-1">
                                            <div class="relative">
                                                <flux:input
                                                    type="number"
                                                    wire:model.live="items.{{ $index }}.quantity"
                                                    placeholder="Qty"
                                                    :iconTrailing="false"
                                                    min="1"
                                                >
                                                    <x-slot name="iconTrailing">
                                                        <span class="text-gray-400 text-xs dark:text-zinc-400">units</span>
                                                    </x-slot>
                                                </flux:input>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="w-1/5 px-6 py-4 align-top">
                                        <div class="space-y-1">
                                            <div class="relative">
                                                <flux:input
                                                    type="number"
                                                    wire:model.live="items.{{ $index }}.unit_price"
                                                    placeholder="0.00"
                                                    :iconLeading="false"
                                                    step="0.01" 
                                                    min="0"
                                                >
                                                    <x-slot name="iconLeading">
                                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                                                    </x-slot>
                                                </flux:input>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="w-1/5 px-6 py-4 align-top">
                                        <div class="space-y-1">
                                            <div class="relative">
                                                <flux:input
                                                    type="number"
                                                    wire:model="items.{{ $index }}.total_price"
                                                    placeholder="0.00"
                                                    readonly
                                                    :iconLeading="false"
                                                >
                                                    <x-slot name="iconLeading">
                                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                                                    </x-slot>
                                                </flux:input>
                                            </div>
                                            
                                            @error("items.{$index}.total_price")
                                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        @if ($index > 0)
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                class="invisible group-hover:visible inline-flex items-center justify-center w-8 h-8 rounded-full text-red-600 hover:text-white hover:bg-red-600 transition-all focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            <button type="button" wire:click="addItem"
                                class="inline-flex items-center text-sm text-[color:var(--color-accent)] hover:text-[#006499] dark:text-blue-400 dark:hover:text-blue-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Item
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="mb-4">
                        <label for="agent_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Agent
                        </label>
                        <flux:select
                            wire:model="agent_id" id="agent_id"
                        >
                            <flux:select.option value="">Select Agent...</flux:select.option>
                        
                            @foreach ($agents as $agent)
                                <flux:select.option
                                    value="{{ $agent->id }}"
                                >
                                    {{ $agent->name }}
                                </flux:select.option>svg xml
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="mb-4">
                        <label for="total_amount"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Total Amount
                        </label>
                        <flux:input
                            type="number"
                            wire:model="total_amount"
                            placeholder="0.00"
                            :iconLeading="false"
                            id="total_amount"
                        >
                            <x-slot name="iconLeading">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                            </x-slot>
                        </flux:input>
                    </div>

                    <div class="mb-4">
                        <label for="tax"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tax (%)
                        </label>
                        <flux:input
                            type="number"
                            wire:model="tax"
                            placeholder="0.00"
                            :iconLeading="false"
                            id="tax"
                        >
                            <x-slot name="iconTrailing">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">%</span>
                            </x-slot>
                        </flux:input>
                    </div>

                    <div class="mb-4">
                        <label for="discount"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Discount
                        </label>
                        <flux:input
                            type="number"
                            wire:model="discount"
                            placeholder="0.00"
                            id="discount"
                        >
                        </flux:input>
                    </div>

                    <div class="mb-4">
                        <label for="valid_until"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Valid Until
                        </label>
                        <flux:input
                            type="date"
                            wire:model="valid_until"
                        >
                        </flux:input>   
                    </div>

                    <div class="mb-4 md:col-span-3">
                        <label for="notes"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notes
                        </label>
                        <textarea wire:model="notes" id="notes" rows="3"
                            class="w-full rounded border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none  dark:border-gray-600"></textarea>
                    </div>
                </div>


            </div>
            <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 gap-1 sm:flex sm:flex-row-reverse sm:px-8">
                <flux:button type="submit" variant="primary">{{ $isEditing ? 'Update' : 'Save' }}</flux:button>
                <flux:button variant="danger" wire:click="cancel">Cancel</flux:button>
            </div>
        </form>
    </div>
</div>