<input type="checkbox" wire:model="selectedProducts"
    value="{{ $stock->id }}" class="rounded"        
    @if (isset($productQuantities[$stock->id]) && $productQuantities[$stock->id] > 0) checked @endif
>