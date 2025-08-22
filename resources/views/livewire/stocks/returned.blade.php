<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\StockAlteration;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        $this->perPage = session('perPageReturned', 10);
    }

    public function updatedPerPage($value)
    {
        $this->perPage = $value;
        session(['perPageReturned' => $value]);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Title('Returned Products')]
    public function with(): array
    {
        return [
            'returnedProducts' => $this->returnedProducts,
        ];
    }

    public function getReturnedProductsProperty()
    {
        return StockAlteration::query()
            ->where('type', 'return')
            ->with(['stock.product']) // assuming alteration links to invoice
            ->when($this->search, function ($q) {
                $q->whereHas('stock.product', function ($p) {
                    $p->where('product_name', 'like', "%{$this->search}%")
                      ->orWhere('product_code', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }
};
?>

<div>
    <x-view-layout
        title="Returned Products"
        description="List of all returned products."
        :items="$returnedProducts"
        searchPlaceholder="Search by Product or Invoice..."
        message="No returned products available."
    >
        <x-slot:emptyIcon>
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600"
                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 2a7 7 0 0 0-7 7v1H1l3.89 3.89.07.14L9 10H6V9a5 5 0 1 1 10 0v1h-3l4 4 4-4h-3V9a7 7 0 0 0-7-7H9Z"/>
                <path d="M15 13v1a5 5 0 1 1-10 0v-1H2l4 4 4-4H7v1a7 7 0 0 0 14 0v-1h-3Z"/>
            </svg>
        </x-slot:emptyIcon>

        <x-list-table
            :headers="[
                'Product Code',
                'Product Name',
                'Invoice Number',
                'Quantity Returned',
                'Reason',
                'Date Returned'
            ]"
            :rows="$returnedProducts->map(function($return) {
                return [
                    $return->stock->product->product_code ?? '-',
                    $return->stock->product->name ?? 'Unknown',
                    $return->stock->invoice_number ?? 'N/A',
                    $return->quantity,
                    $return->reason ?? 'N/A',
                    $return->created_at->format('M d, Y'),
                ];
            })"
        />
    </x-view-layout>
</div>
