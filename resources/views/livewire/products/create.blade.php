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

        $this->product_uid = $this->generateProductUID();
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
    
    private function generateProductUID()
    {
        do {
            $uid = 'P-' . strtoupper(Str::random(8));
        } while (Product::where('product_uid', $uid)->exists());
        return $uid;
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
            dd($e->errors());
        }

        $product = Product::create([
            'product_uid' => $this->generateProductUid(),
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
            'product_code', 'name', 'description', 'volume_weight',
            'product_type', 'unit', 'brand', 'category',
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
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $path = $this->importFile->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headers = array_shift($rows);

        //Preload existing data 
        $productTypes = \App\Models\ProductType::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $units       = \App\Models\Unit::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $brands      = \App\Models\Brand::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $categories  = \App\Models\Category::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        $newProductTypes = [];
        $newUnits = [];
        $newBrands = [];
        $newCategories = [];

        foreach ($rows as $row) {
            $data = array_combine($headers, $row);

            if (!isset($data['name']) || empty(trim($data['name']))) {
                continue;
            }

            $data = array_map(fn($v) => trim((string) $v) === '' ? null : trim($v), $data);

            $productTypeId = null;
            if ($data['product_type']) {
                $key = strtolower($data['product_type']);
                $productTypeId = $productTypes[$key] ?? null;

                if (!$productTypeId && !isset($newProductTypes[$key])) {
                    $newProductTypes[$key] = ['name' => ucfirst($key)];
                }
            }

            $unitId = null;
            if ($data['unit']) {
                $key = strtolower($data['unit']);
                $unitId = $units[$key] ?? null;

                if (!$unitId && !isset($newUnits[$key])) {
                    $newUnits[$key] = ['name' => ucfirst($key)];
                }
            }

            $brandId = null;
            if ($data['brand']) {
                $key = strtolower($data['brand']);
                $brandId = $brands[$key] ?? null;

                if (!$brandId && !isset($newBrands[$key])) {
                    $newBrands[$key] = ['name' => ucfirst($key)];
                }
            }

            $categoryId = null;
            if ($data['category']) {
                $key = strtolower($data['category']);
                $categoryId = $categories[$key] ?? null;

                if (!$categoryId && !isset($newCategories[$key])) {
                    $newCategories[$key] = ['name' => ucfirst($key)];
                }
            }

            $products[] = [
                'product_uid'        => $this->generateProductUid(),
                'product_code'       => $data['product_code'] ?? null,
                'name'               => $data['name'],
                'description'        => $data['description'],
                'volume_weight'      => $data['volume_weight'],
                'product_type_id'    => $productTypeId,
                'unit_id'            => $unitId,
                'brand_id'           => $brandId,
                'category_id'        => $categoryId,
                'quantity_per_piece' => $data['quantity_per_piece'] ?? 1,
                'low_stock_value'    => $data['low_stock_value'] ?? 10,
                'stock_value'        => $data['stock_value'] ?? 0,
                'capital_price'      => $data['capital_price'] ?? 0,
                'selling_price'      => $data['selling_price'] ?? 0,
                'is_vatable'         => $data['is_vatable'] ?? 0,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        if ($newProductTypes) {
            $created = \App\Models\ProductType::insert(array_values($newProductTypes));
            $productTypes = \App\Models\ProductType::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        }
        if ($newUnits) {
            \App\Models\Unit::insert(array_values($newUnits));
            $units = \App\Models\Unit::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        }
        if ($newBrands) {
            \App\Models\Brand::insert(array_values($newBrands));
            $brands = \App\Models\Brand::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        }
        if ($newCategories) {
            \App\Models\Category::insert(array_values($newCategories));
            $categories = \App\Models\Category::all()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        }

        \App\Models\Product::insert($products);

        $this->importFile = null;
        flash()->success('Products imported successfully!');
    }


    public function exportProducts()
    {
        $products = \App\Models\Product::with(['type', 'unit', 'brand', 'category'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Product Code', 'Name', 'Description', 'Volume/Weight',
            'Product Type', 'Unit', 'Brand', 'Category',
            'Quantity Per Piece', 'Low Stock Value',
        ];

        $sheet->fromArray([$headers], NULL, 'A1');

        $rowIndex = 2;
        foreach ($products as $product) {
            $sheet->fromArray([[
                $product->product_code,
                $product->name,
                $product->description,
                $product->volume_weight,
                $product->type->name ?? '',
                $product->unit->name ?? '',
                $product->brand->name ?? '',
                $product->category->name ?? '',
                $product->quantity_per_piece,
                $product->low_stock_value,
            ]], NULL, "A{$rowIndex}");
            $rowIndex++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'products_export_' . now()->format('Y-m-d_His') . '.xlsx';
        $tempFile = storage_path("app/public/$fileName");
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
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

            <button 
                wire:click="exportProducts"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow">
                Export Products
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
