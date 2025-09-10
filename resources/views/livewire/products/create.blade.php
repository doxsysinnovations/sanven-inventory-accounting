<?php

use Livewire\Volt\Component;
use App\Models\Product;

new class extends Component {
    use Livewire\WithFileUploads;

    public $product_code;
    public $name;
    public $description;
    public $supplier;
    public $capital_price;
    public $selling_price;
    public $expiration_date;
    public $quantity;
    public $product_type;
    public $unit;
    public $brand;
    public $category;
    public $quantity_per_piece = 1;
    public $low_stock_value = 10;
    public $image;
    public $is_vatable;

    public $volume_weight;

    public $productTypes = [];
    public $units = [];
    public $brands = [];
    public $categories = [];
    public $types = [];
    public $suppliers = [];

    public $showInitialStock = false;

    protected $casts = [
        'showInitialStock' => 'boolean',
    ];

    protected function rules()
    {
        $rules = [
            'product_code' => 'required|unique:products',
            'name' => 'required',
            'description' => 'nullable',
            'product_type' => 'required',
            'unit' => 'required',
            'brand' => 'required',
            'is_vatable' => 'required',
            'category' => 'required',
            'quantity_per_piece' => 'required|integer|min:1',
            'low_stock_value' => 'required|integer|min:0',

            'volume_weight' => 'nullable|string|max:255',
        ];

        if ($this->showInitialStock === true) {
            $rules = array_merge($rules, [
                'supplier' => 'nullable|integer|exists:suppliers,id',
                'quantity' => 'nullable|integer|min:1',
                'capital_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'expiration_date' => 'nullable|date|after:today',
            ]);
        }

        return $rules;
    }

    public function mount()
    {
        $this->productTypes = \App\Models\ProductType::all();
        $this->units = \App\Models\Unit::all();
        $this->brands = \App\Models\Brand::orderBy('name')->get();
        $this->categories = \App\Models\Category::orderBy('name')->get();
        $this->types = \App\Models\ProductType::orderBy('name')->get();
        $this->suppliers = \App\Models\Supplier::orderBy('name')->get();
    }

    public function messages()
    {
        return [
            'product_code.required' => 'Please enter the product code.',
            'product_code.unique' => 'This product code is already in use. Please enter a unique one.',

            'name.required' => 'Please enter the product name.',

            'product_type.required' => 'Please select the product type.',
            'unit.required' => 'Please specify the unit of measurement.',
            'brand.required' => 'Please select the product brand.',
            'is_vatable.required' => 'Please specify if the product has VAT.',
            'category.required' => 'Please choose a product category.',

            'quantity_per_piece.required' => 'Please enter how many pieces per quantity.',
            'quantity_per_piece.integer' => 'Quantity per piece must be a whole number.',
            'quantity_per_piece.min' => 'Quantity per piece must be at least 1.',

            'low_stock_value.required' => 'Please enter the low stock threshold.',
            'low_stock_value.integer' => 'Low stock value must be a whole number.',
            'low_stock_value.min' => 'Low stock value cannot be negative.',
        ];
    }

    private function generateStockNumber()
    {
        $yearPrefix = date('Y');
        $lastId = \App\Models\Stock::max('id') ?? 0;
        $newId = $lastId + 1;

        $lastProduct = \App\Models\Stock::orderBy('id', 'desc')->first();

        if ($lastProduct) {
            $lastStockNumber = intval(substr($lastProduct->stock_number, -6));
            $newStockNumber = $lastStockNumber + 1;
        } else {
            $newStockNumber = 1;
        }

        return $yearPrefix
            . $newId
            . str_pad($newStockNumber, 6, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        // $this->validate();
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // 👈 shows exactly what field(s) are failing
        }

        $product = Product::create([
            'product_code' => $this->product_code,
            'name' => $this->name,
            'description' => $this->description,
            'product_type_id' => $this->product_type,
            'unit_id' => $this->unit,
            'brand_id' => $this->brand,
            'is_vatable' => $this->is_vatable,
            'category_id' => $this->category,
            'quantity_per_piece' => $this->quantity_per_piece,
            'low_stock_value' => $this->low_stock_value,
            'volume_weight' => $this->volume_weight,
        ]);

        if ($this->image) {
            $product->addMedia($this->image)->toMediaCollection('product-image');
        }

        if ($this->showInitialStock && (
                $this->supplier || $this->quantity || $this->capital_price || 
                $this->selling_price || $this->expiration_date
            )) {
            $product->stocks()->create([
                'stock_number' => $this->generateStockNumber(),
                'supplier_id' => $this->supplier,
                'quantity' => $this->quantity,
                'capital_price' => $this->capital_price,
                'selling_price' => $this->selling_price,
                'expiration_date' => $this->expiration_date,
            ]);
        }

        $this->resetForm();
        flash()->success('Product created successfully!');
    }

    private function resetForm()
    {
        $this->product_code = '';
        $this->name = '';
        $this->image = null;
        $this->dispatch('reset-preview');
        $this->description = '';
        $this->product_type = '';
        $this->unit = '';
        $this->brand = '';
        $this->is_vatable = null;
        $this->category = '';
        $this->supplier = '';
        $this->quantity = '';
        $this->capital_price = '';
        $this->selling_price = '';
        $this->quantity_per_piece = 1;
        $this->low_stock_value = 10;
        $this->expiration_date='';
        $this->volume_weight = '';
        $this->product = null;
        $this->resetValidation();
        $this->showInitialStock = false;
    }

    public function cancel() 
    {
        $this->resetForm();
    }
}; ?>

<div>
    <x-products-form 
        :is-editing="false"
        :brands="$brands"
        :categories="$categories"
        :types="$types"
        :units="$units"
        :suppliers="$suppliers"
    />
</div>
