<flux:input 
    type="number"
    wire:model="productQuantities.{{ $stock->id }}"
    min="1"
    max="{{ $stock->quantity - collect($cart)->where('stock_id', $stock->id)->sum('quantity') }}"
    wire:change="updateProductSelection('{{ $stock->id }}')"
/>