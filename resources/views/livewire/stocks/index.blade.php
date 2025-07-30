<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Stock;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $product;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $productToDelete;

    // Filter properties
    public $category = '';
    public $brand = '';
    public $unit = '';
    public $productType = '';
    public $perPage = 5;

    public function mount()
    {
        $this->perPage = session('perPage', 5);
    }

    public function updatedPerPage($value)
    {
        $this->perPage = $value;
        session(['perPage' => $value]);
        $this->resetPage();
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedBrand()
    {
        $this->resetPage();
    }

    public function updatedUnit()
    {
        $this->resetPage();
    }

    public function updatedProductType()
    {
        $this->resetPage();
    }

    public function confirmDelete($productId)
    {
        $this->productToDelete = $productId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $product = Stock::find($this->productToDelete);
        if ($product) {
            $product->delete();
            flash()->success('Product deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->productToDelete = null;
    }

    public function edit($productId)
    {
        return redirect()->route('products.edit', $productId);
    }

    #[Title('Products')]
    public function with(): array
    {
        return [
            'stocks' => $this->stocks,
        ];
    }

    public function getStocksProperty()
    {
        return Stock::query()
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('product_name', 'like', '%' . $this->search . '%')->orWhere('product_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->brand, fn($q) => $q->where('brand_id', $this->brand))
            ->when($this->unit, fn($q) => $q->where('unit_id', $this->unit))
            ->when($this->productType, fn($q) => $q->where('product_type_id', $this->productType))
            ->latest()
            ->paginate($this->perPage);
    }
};
?>

<div>
    <x-view-layout
        title="List of Stocks"
        description="Manage your stocks here."
        :items="$stocks"
        searchPlaceholder="Search Products/Stocks..."
        message="No stocks available."
        createButtonLabel="Receive Stock"
        createButtonAbility="products.create"
        createButtonRoute="stocks.create"
        :showNewCreateButtonIfEmpty="true"
        createButtonLabelIfEmpty="Add Product"
        createButtonAbilityIfEmpty="products.create"
        createButtonRoute="stocks.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M20 10H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8ZM9 13v-1h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                <path d="M2 6a2 2 0 0 1 2-2h16a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Z"/>
            </svg>
        </x-slot:emptyIcon>
         <x-list-table
            :headers="[
                'Code', 'Name', 'Capital', 'Selling', 'Stocks',
                'Manufactured Date', 'Expiry Date', 'Actions'
            ]"
            :rows="$stocks->map(function($stock) {
                $expirationDate = \Carbon\Carbon::parse($stock->expiration_date);
                $daysDiff = $expirationDate->diffInDays(now(), false);

                if ($daysDiff >= 0) {
                    $status = 'expired';
                } elseif ($daysDiff >= -3) {
                    $status = 'expiring';
                } else {
                    $status = 'valid';
                }

                return [
                    $stock->product->product_code,
                    $stock->product_name ?? 'N/A',
                    number_format($stock->capital_price, 2),
                    number_format($stock->selling_price, 2),
                    $stock->quantity,
                    $stock->formatted_manufactured_date,
                    [
                        'date' => $expirationDate->format('F j, Y'),
                        'status' => $status,
                    ],
                    '__model' => $stock
                ];
            })"
            editAbility="products.edit"
            editParameter="id"
            editRoute="products.edit"
            deleteAbility="products.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Stock"
            message="Are you sure you want to delete this stock? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>