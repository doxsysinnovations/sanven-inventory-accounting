<?php

use Livewire\Volt\Component;
use App\Models\Product;

use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
    public $importFile;

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

            'importFile' => 'nullable|file|mimes:xlsx,xls,csv|max:2048',
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

        $this->product_code = $this->generateProductCode();
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
    
    private function generateProductCode()
    {
        do {
            $code = 'P-' . strtoupper(Str::random(8));
        } while (Product::where('product_code', $code)->exists());
        return $code;
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

    public function downloadTemplate()
    {
        $headers = [
            'name', 'description', 'volume_weight',
            'product_type_id', 'unit_id', 'brand_id', 'category_id',
            'quantity_per_piece', 'low_stock_value',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$headers], NULL, 'A1');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'product_import_template.xlsx';
        $tempFile = storage_path("app/public/$fileName");
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function importProducts()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $path = $this->importFile->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headers = array_shift($rows);
        foreach ($rows as $row) {
            $data = array_combine($headers, $row);

            if (!isset($data['name']) || empty(trim($data['name']))) {
                continue;
            }

            $data = array_map(fn($v) => trim((string) $v) === '' ? null : trim($v), $data);

            $productTypeId = null;
            if ($data['product_type_id']) {
                $productTypeId = \App\Models\ProductType::whereRaw('LOWER(id) = ?', [strtolower($data['product_type_id'])])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($data['product_type_id'])])
                    ->value('id');

                if (!$productTypeId) {
                    $productTypeId = \App\Models\ProductType::create([
                        'name' => ucfirst(strtolower($data['product_type_id'])),
                    ])->id;
                }
            }

            $unitId = null;
            if ($data['unit_id']) {
                $unitId = \App\Models\Unit::whereRaw('LOWER(id) = ?', [strtolower($data['unit_id'])])
                    ->orWhereRaw('LOWER(code) = ?', [strtolower($data['unit_id'])])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($data['unit_id'])])
                    ->value('id');

                if (!$unitId) {
                    $unitId = \App\Models\Unit::create([
                        'name' => ucfirst(strtolower($data['unit_id'])),
                        'code' => strtoupper(Str::slug($data['unit_id'], '_')),
                    ])->id;
                }
            }

            $brandId = null;
            if ($data['brand_id']) {
                $brandId = \App\Models\Brand::whereRaw('LOWER(id) = ?', [strtolower($data['brand_id'])])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($data['brand_id'])])
                    ->value('id');

                if (!$brandId) {
                    $brandId = \App\Models\Brand::create([
                        'name' => ucfirst(strtolower($data['brand_id'])),
                    ])->id;
                }
            }

            $categoryId = null;
            if ($data['category_id']) {
                $categoryId = \App\Models\Category::whereRaw('LOWER(id) = ?', [strtolower($data['category_id'])])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($data['category_id'])])
                    ->value('id');

                if (!$categoryId) {
                    $categoryId = \App\Models\Category::create([
                        'name' => ucfirst(strtolower($data['category_id'])),
                    ])->id;
                }
            }

            Product::create([
                'product_code'       => $this->generateProductCode(),
                'name'               => $data['name'],
                'description'        => $data['description'] ?? null,
                'volume_weight'      => $data['volume_weight'] ?? null,
                'product_type_id'    => $productTypeId,
                'unit_id'            => $unitId,
                'brand_id'           => $brandId,
                'category_id'        => $categoryId,
                'quantity_per_piece' => $data['quantity_per_piece'] ?: 1,
                'low_stock_value'    => $data['low_stock_value'] ?: 10,
                'is_vatable'         => $data['is_vatable'] ?? 1,
            ]);
        }

        $this->importFile = null;
        flash()->success('Products imported successfully!');
    }
}; ?>

<div>
    <!-- Excel Import & Template Section -->
    <div class="p-4 bg-gray-100 rounded-lg dark:bg-gray-800 mb-6">
        <h2 class="font-bold text-lg mb-6 text-gray-700 dark:text-gray-200">Bulk Import Products</h2>
        <div class="flex flex-col sm:flex-row gap-4">

            <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="block text-sm text-gray-700 dark:text-gray-300">

            <button 
                wire:click="importProducts"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow">
                Import Products
            </button>

            <button 
                wire:click="downloadTemplate"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow">
                Download Template
            </button>
            
        </div>

        @error('importFile')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    <x-products-form 
        :is-editing="false"
        :brands="$brands"
        :categories="$categories"
        :types="$types"
        :units="$units"
        :suppliers="$suppliers"
    />
</div>
