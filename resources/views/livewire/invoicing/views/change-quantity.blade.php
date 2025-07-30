<flux:input 
    type="number" wire:model="cart.{{ $key }}.quantity"
    min="1"
    max="{{ $item['available_quantity'] + $item['quantity'] }}"
    wire:change="updateCartItem('{{ $key }}')"
/>