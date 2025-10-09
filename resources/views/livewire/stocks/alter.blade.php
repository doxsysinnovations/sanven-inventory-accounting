<?php

use App\Models\Stock;
use App\Models\StockAlteration;
use Livewire\Volt\Component;

new class extends Component {
    public Stock $stock;
    public $alterationType = '';
    public $quantity;
    public $reason;

    public function mount($id)
    {
        $this->stock = Stock::with('product')->findOrFail($id);
    }

    public function alterStock()
    {
        $this->validate([
            'alterationType' => 'required|in:return,broken',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        StockAlteration::create([
            'stock_id' => $this->stock->id,
            'type' => $this->alterationType,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'user_id' => auth()->id(),
        ]);

        if ($this->alterationType === 'return') {
            $this->stock->increment('quantity', $this->quantity);
        } else {
            $this->stock->decrement('quantity', $this->quantity);
        }

        session()->flash('success', 'Stock altered successfully.');
        return redirect()->route('stocks');
    }
};
?>

<div>
    <form wire:submit.prevent="alterStock">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                Alter Stock – {{ $stock->stock_number }}
            </h3>
        </div>

        <div class="bg-white dark:bg-gray-900 px-6 py-8 shadow-sm">
            <!-- Product details (disabled) -->
            <div class="mb-6">
                <h1 class="font-bold text-lg mb-4">Stock Information</h1>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <flux:input :value="$stock->invoice_number" :label="__('Invoice Number')" disabled />
                    <flux:input :value="$stock->product->name" :label="__('Product Name')" disabled />
                    <flux:input :value="$stock->product->product_code" :label="__('Product Code')" disabled />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:input :value="$stock->supplier->trade_name" :label="__('Supplier')" disabled />
                    <flux:input :value="$stock->quantity" :label="__('Current Quantity')" disabled />
                    <flux:input :value="$stock->stock_location" :label="__('Stock Location')" disabled />
                </div>
            </div>

            <!-- Alteration -->
            <div class="mb-6">
                <h1 class="font-bold text-lg mb-4">Alteration</h1>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">

                    <flux:select wire:model.live="alterationType" :label="__('Alteration Type')" size="md">
                        <flux:select.option value="">Choose...</flux:select.option>
                        <flux:select.option value="return">Return Product</flux:select.option>
                        <flux:select.option value="broken">Broken Stock</flux:select.option>
                    </flux:select>

                    <div wire:key="alteration-fields" class="grid grid-cols-1 gap-6">
                        @switch($alterationType)
                            @case('return')
                            @case('broken')
                                <flux:input wire:model="quantity" :label="__('Quantity')" type="number" min="1" />
                                <flux:textarea wire:model="reason" label="Reason" rows="4"
                                    placeholder="Enter reason for {{ $alterationType }}" class="col-span-1" />
                            @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 rounded-b-lg dark:bg-(--color-accent-4-dark) p-8 flex justify-end space-x-2">
            <flux:button class="sm:w-auto" variant="danger" type="button" wire:click="$redirectRoute('stocks')">Cancel</flux:button>
            <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit">Save Alteration</flux:button>
        </div>
    </form>
</div>
