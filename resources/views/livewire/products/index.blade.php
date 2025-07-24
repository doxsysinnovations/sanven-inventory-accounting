<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Product;
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

    public function confirmDelete($id)
    {
        $this->productToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $product = Product::find($this->productToDelete);
        if ($product) {
            $product->delete();
            flash()->success('Product deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->productToDelete = null;
    }

    public function edit($id)
    {
        return redirect()->route('products.edit', $id);
    }

    #[Title('Products')]
    public function with(): array
    {
        return [
            'products' => $this->products,
        ];
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_code', 'like', '%' . $this->search . '%');
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
        title="All Products"
        :items="$products"
        :perPage="$perPage"
        searchPlaceholder="Search Products..."
        message="No products available."
        createButtonLabel="Add Product"
        createButtonAbility="products.create"
        createButtonRoute="products.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.25-2.095c.478-.86.75-1.85.75-2.905a5.973 5.973 0 0 0-.75-2.906 4 4 0 1 1 0 5.811ZM15.466 20c.34-.588.535-1.271.535-2v-1a5.978 5.978 0 0 0-1.528-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.535Z" clip-rule="evenodd"/>
            </svg>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M4.857 3A1.857 1.857 0 0 0 3 4.857v4.286C3 10.169 3.831 11 4.857 11h4.286A1.857 1.857 0 0 0 11 9.143V4.857A1.857 1.857 0 0 0 9.143 3H4.857Zm10 0A1.857 1.857 0 0 0 13 4.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 21 9.143V4.857A1.857 1.857 0 0 0 19.143 3h-4.286Zm-10 10A1.857 1.857 0 0 0 3 14.857v4.286C3 20.169 3.831 21 4.857 21h4.286A1.857 1.857 0 0 0 11 19.143v-4.286A1.857 1.857 0 0 0 9.143 13H4.857Zm10 0A1.857 1.857 0 0 0 13 14.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 21 19.143v-4.286A1.857 1.857 0 0 0 19.143 13h-4.286Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Image', 'Code', 'Name', 'Capital', 'Selling', 'Stocks', 'Low', 'Actions']"
            :rows="$products->map(fn($product) => [
                '<img src=\'' . asset('storage/' . $product->image) . '\' alt=\'Image\' class=\'w-10 h-10 rounded\' />',
                $product->product_code,
                $product->name,
                number_format(optional($product->stocks()->latest()->first())->capital_price ?? 0, 2),
                number_format(optional($product->stocks()->latest()->first())->selling_price ?? 0, 2),
                '<span class=\'' . ($product->stocks->sum('quantity') <= $product->low_stock_value ? 'text-(--color-accent-2)' : '') . '\'>'
                    . $product->stocks->sum('quantity') .
                '</span>',
                '<span class=\'' . ($product->stocks->sum('quantity') <= $product->low_stock_value ? 'text-(--color-accent-2)' : '') . '\'>'
                    . $product->low_stock_value .
                '</span>',
                '__model' => $product
            ])"
            editAbility="products.edit"
            editParameter="id"
            editRoute="products.edit"
            deleteAbility="products.delete"
            deleteAction="confirmDelete"
        />

    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Product"
            message="Are you sure you want to delete this product? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>