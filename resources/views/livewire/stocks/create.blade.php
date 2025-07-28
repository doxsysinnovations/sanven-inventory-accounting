<?php

use Livewire\Volt\Component;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Location;

new class extends Component {
    public string $stock_number = '';
    public int $product_id = 0;
    public string $product_name = '';
    public ?string $product_description = null;
    public string $brand_name = '';
    public string $product_category = '';
    public string $product_code = '';
    public string $batch_number = '';
    public int $quantity = 0;
    public float $capital_price = 0;
    public float $selling_price = 0;
    public string $expiry_date = '';
    public $suppliers = [];
    public string $search = '';
    public $products = [];
    public bool $openModal = false; // Modal visibility state
    public string $stock_location = '';
    public string $manufactured_date = '';
    public string $invoice_number = '';
    public string $batch_notes = '';
    public $locations = [];
    public $units = []; // List of units
    public int $unit_id = 0; // Selected unit
    public string $supplier = '';
    public int $currentStep = 1;

    public function mount()
    {
        $this->products = Product::all(); // Load all products initially
        $this->suppliers = Supplier::all(); // Load all suppliers
        $this->units = Unit::all(); // Load all units
        $this->locations = Location::all();
    }

    public function nextStep()
    {
        $this->validateStep();
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    private function validateStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'product_name' => 'required|string|max:255',
                'product_code' => 'required|string|max:255',
            ]);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'quantity' => 'required|integer|min:1',
                'unit_id' => 'required|integer|exists:units,id', // Ensure the unit exists in the units table
                'capital_price' => 'required|numeric|min:1',
                'selling_price' => 'required|numeric|min:1',
                'expiry_date' => 'required|date|after:today',
                'manufactured_date' => 'required|date|before_or_equal:today',
                'invoice_number' => 'nullable|string|max:100',
                'supplier' => 'required|string|max:255',
            ]);
        }
    }

    public function updatedSearch($value)
    {
        // Check if the input matches a product code (barcode)
        $product = Product::where('product_code', $value)->first();

        if ($product) {
            $this->selectProduct($product->id); // Automatically select the product
            $this->search = ''; // Clear the search field
            return;
        }

        // Perform a normal search for product names or partial product codes
        $this->products = Product::where('name', 'like', '%' . $value . '%')
            ->orWhere('product_code', 'like', '%' . $value . '%')
            ->get();
    }
    public function updatedCapitalPrice($value)
    {
        $this->capital_price = number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    public function updatedSellingPrice($value)
    {
        $this->selling_price = number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    public function updatedQuantity($value)
    {
        $this->quantity = (int) str_replace(',', '', $value); // Ensure the value is cast to an integer
    }
    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        $this->product_id = $product->id;
        $this->product_name = $product->name;
        $this->brand_name = $product->brand->name;
        $this->product_category = $product->category->name;
        $this->product_description = $product->description;
        $this->product_code = $product->product_code; // Reset batch number for the new batch

        $this->openModal = false; // Close the modal after selection
        flash()->success('You have selected ' . $product->name);
    }

    public function rules()
    {
        return [
            'product_name' => 'required|string|max:255',
            // 'batch_number' => 'required|string|max:100|unique:stocks,batch_number',
            'quantity' => 'required|integer|min:1',
            'unit_id' => 'required|integer|exists:units,id', // Ensure the unit exists in the units table
            'capital_price' => 'required|numeric|min:1',
            'selling_price' => 'required|numeric|min:1',
            'expiry_date' => 'required|date|after:today',
            'manufactured_date' => 'required|date|before_or_equal:today',
            'stock_location' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'batch_notes' => 'nullable|string|max:1000',
            'supplier' => 'required|string|max:255',
        ];
    }

    public function messages()
{
    return [
        'product_name.required' => 'The product name is required.',
        'product_name.string' => 'The product name must be a string.',
        'product_name.max' => 'The product name may not be greater than 255 characters.',

        // 'batch_number.required' => 'The batch number is required.',
        // 'batch_number.string' => 'The batch number must be a string.',
        // 'batch_number.max' => 'The batch number may not be greater than 100 characters.',
        // 'batch_number.unique' => 'This batch number already exists.',

        'quantity.required' => 'Quantity is required.',
        'quantity.integer' => 'Quantity must be a whole number.',
        'quantity.min' => 'Quantity must be at least 1.',

        'unit_id.required' => 'Unit is required.',
        'unit_id.integer' => 'Unit ID must be a valid number.',
        'unit_id.exists' => 'The selected unit does not exist.',

        'capital_price.required' => 'Capital price is required.',
        'capital_price.numeric' => 'Capital price must be a valid number.',
        'capital_price.min' => 'Capital price must be at least 1.',

        'selling_price.required' => 'Selling price is required.',
        'selling_price.numeric' => 'Selling price must be a valid number.',
        'selling_price.min' => 'Selling price must be at least 1.',

        'expiry_date.required' => 'Expiry date is required.',
        'expiry_date.date' => 'Expiry date must be a valid date.',
        'expiry_date.after' => 'Expiry date must be after today.',

        'manufactured_date.required' => 'Manufactured date is required.',
        'manufactured_date.date' => 'Manufactured date must be a valid date.',
        'manufactured_date.before_or_equal' => 'Manufactured date must be today or earlier.',

        'stock_location.string' => 'Stock location must be a string.',
        'stock_location.max' => 'Stock location may not be greater than 255 characters.',

        'invoice_number.string' => 'Invoice number must be a string.',
        'invoice_number.max' => 'Invoice number may not be greater than 100 characters.',

        'batch_notes.string' => 'Batch notes must be a string.',
        'batch_notes.max' => 'Batch notes may not be greater than 1000 characters.',

        'supplier.required' => 'Supplier is required.',
        'supplier.string' => 'Supplier name must be a string.',
        'supplier.max' => 'Supplier name may not be greater than 255 characters.',
    ];
}

    private function generateStockNumber()
    {
        $yearPrefix = date('Y');
        $lastProduct = \App\Models\Stock::orderBy('id', 'desc')->first();
        if ($lastProduct) {
            $lastStockNumber = intval(substr($lastProduct->stock_number, -6));
            $newStockNumber = $lastStockNumber + 1;
        } else {
            $newStockNumber = 1;
        }
        return $yearPrefix . str_pad($newStockNumber, 6, '0', STR_PAD_LEFT);
    }

    public function goToStep($step)
    {
        if ($step <= $this->currentStep) {
            $this->currentStep = $step;
            return;
        }
        
        $this->validateStep();
        $this->currentStep = $step;
    }

    public function save()
    {

        $this->validate();
        // Check if the product is expired
        Stock::create([
            'product_name' => $this->product_name,
            'product_id' => $this->product_id,
            'batch_number' => $this->batch_number,
            'stock_number' => $this->generateStockNumber(),
            'quantity' => $this->quantity,
            'unit_id' => $this->unit_id, // Save the selected unit
            'capital_price' => $this->capital_price,
            'selling_price' => $this->selling_price,
            'expiration_date' => $this->expiry_date,
            'manufactured_date' => $this->manufactured_date,
            'stock_location' => $this->stock_location,
            'invoice_number' => $this->invoice_number,
            'batch_notes' => $this->batch_notes,
            'supplier_id' => $this->supplier,
        ]);
        
        flash()->success('Stock added successfully! ');
        $this->currentStep = 1; // Reset to the first step

        $this->reset(['product_name','product_code','brand_name','product_category', 'batch_number','product_description', 'quantity', 'unit_id', 'capital_price', 'selling_price', 'expiry_date', 'manufactured_date', 'stock_location', 'invoice_number', 'batch_notes', 'supplier']);
    }
};
?>
<div>
    <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
         <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
           Receive Stock
        </h3>
    </div>
    <div class="bg-white dark:bg-(--color-accent-dark) p-8 sm:p-10">
        <div class="flex flex-col space-x-4 mb-4">
            <div class="w-full bg-gray-200 rounded-full h-2 mb-6">
                <div class="bg-(--color-accent) h-2 rounded-full transition-all duration-500" 
                    style="width: {{ ($currentStep / 3) * 100 }}%">
                </div>
            </div>
        
            <div class="flex flex-col sm:flex-row items-stretch gap-2 mb-6 bg-gray-100 p-1 rounded-lg">
                <button wire:click="goToStep(1)" 
                    class="flex-1 px-6 py-3 rounded-sm text-base shadow
                    {{ $currentStep === 1 ? 'bg-(--color-accent) text-white dark:bg-blue-500' : 'bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-300 hover:bg-gray-700 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center bg-white border text-sm
                            {{ $currentStep === 1 ? 'font-bold text-(--color-accent) dark:text-(--color-accent-3-dark)' : 'font-medium text-gray-900' }}">
                            1
                        </div>
                        <div>
                            <span class="{{ $currentStep === 1 ? 'font-bold' : 'font-medium' }}">
                                Product Info
                            </span>
                        </div>
                    </div>
                </button>

                <button wire:click="goToStep(2)" 
                    class="flex-1 px-6 py-3 rounded-sm text-base transition-all duration-300 shadow w-full
                    {{ $currentStep === 2 ? 'bg-(--color-accent) text-white dark:bg-blue-500' : 'bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-300 hover:bg-gray-700 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center bg-white border text-sm
                            {{ $currentStep === 2 ? 'font-bold text-(--color-accent) dark:text-(--color-accent-3-dark)' : 'font-medium text-gray-900' }}">
                            2
                        </div>
                        <div>
                            <span class="{{ $currentStep === 2 ? 'font-bold' : 'font-medium' }}">
                                Stock Info
                            </span>
                        </div>
                    </div>
                </button>

                <button wire:click="goToStep(3)" 
                    class="flex-1 px-6 py-3 rounded-sm text-base transition-all duration-300 shadow w-full
                    {{ $currentStep === 3 ? 'bg-(--color-accent) text-white dark:bg-blue-500' : 'bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-300 hover:bg-gray-700 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center bg-white border text-sm
                            {{ $currentStep === 2 ? 'font-bold text-(--color-accent) dark:text-(--color-accent-3-dark)' : 'font-medium text-gray-900' }}">
                            3
                        </div>
                        <div>
                            <span class="{{ $currentStep === 3 ? 'font-bold' : 'font-medium' }}">
                                Additional Details
                            </span>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        @include('livewire.stocks.views.step-1-product-information')
        @include('livewire.stocks.views.step-2-stock-information')
        @include('livewire.stocks.views.step-3-additional-details')

    </div>
</div>