<?php

use Livewire\Volt\Component;
use App\Models\Brand;
use App\Models\Category;
use Livewire\Attributes\Title;

new class extends Component {
    use Livewire\WithFileUploads;

    public $currentTab = 'product_information';

    // Form Fields
    public $code = '';
    public $name = '';
    public $description = '';
    public $images = [];
    public $price = '';
    public $quantity = '';
    public $sku = '';
    public $category = '';
    public $status = 'active';

    public $subCategories = [];
    public $selectedBrand = '';
    public $selectedCategory = '';
    public $selectedSubCategory = '';

    public function nextTab()
    {
        if ($this->currentTab === 'product_information') {
            $this->currentTab = 'price_quantity';
        } elseif ($this->currentTab === 'price_quantity') {
            $this->currentTab = 'images';
        }
    }

    public function previousTab()
    {
        if ($this->currentTab === 'images') {
            $this->currentTab = 'price_quantity';
        } elseif ($this->currentTab === 'price_quantity') {
            $this->currentTab = 'product_information';
        }
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'array',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'sku' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        // Save logic here (e.g., database insertion)
        session()->flash('success', 'Product saved successfully!');
    }

    public function getSubCategoriesProperty()
    {
        return Category::where('is_active', true)->where('is_parent', true)->get();
    }

    public function updatingSelectedCategory($id)
    {
        $this->subCategories = Category::where('parent_id', $id)->get();
    }
    #[Title('Create Product')]
    public function with()
    {
        $brands = Brand::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        return [
            'brands' => $brands,
            'categories' => $categories,
        ];
    }
};
?>

<div>
    <div class="mb-4">
        <nav class="flex items-center justify-between" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <a href="{{ route('products') }}"
                            class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 md:ml-2">Products</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
            <flux:button href="{{ route('products') }}" variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
                Back
            </flux:button>
        </nav>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <!-- Tab Navigation -->
        <div x-data="{ tab: $wire.currentTab }">
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button @click="tab = 'product_information'"
                    :class="{ 'border-b-2 border-blue-500 text-blue-500': tab === 'product_information' }"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">
                    Product Information
                </button>

                <button @click="tab = 'price_quantity'"
                    :class="{ 'border-b-2 border-blue-500 text-blue-500': tab === 'price_quantity' }"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">
                    Price & Quantity
                </button>

                <button @click="tab = 'images'"
                    :class="{ 'border-b-2 border-blue-500 text-blue-500': tab === 'images' }"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">
                    Images
                </button>
            </div>

            <!-- Tab Panels -->
            <div class="mt-4">
                <!-- Product Information Tab -->
                <div x-show="tab === 'product_information'" class="p-4">

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <flux:input wire:model="code" :label="__('Product Code')" type="text"
                                placeholder="ex 123123"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                        <div>
                            <flux:input wire:model="name" :label="__('Product Name')" type="text"
                                placeholder="ex Product 1"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <flux:select wire:model.live="selectedBrand" :label="__('Brand')" size="md"
                                placeholder="Choose brand...">
                                @foreach ($brands as $brand)
                                    <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:select wire:model.live="selectedCategory" :label="__('Category')" size="md"
                                placeholder="Choose category...">
                                @foreach ($categories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:select wire:model.live="selectedSubCategory" :label="__('Sub Category (optional)')"
                                size="md" placeholder="Choose sub category...">
                                @foreach ($subCategories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1">
                        <flux:textarea label="Description" placeholder="type description..." />
                    </div>
                </div>

                <!-- Price & Quantity Tab -->
                <div x-show="tab === 'price_quantity'" class="p-4">

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <flux:input wire:model="capital_price" :label="__('Capital Price')" type="number"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                        <div>
                            <flux:input wire:model="selling_price" :label="__('Selling Price')" type="number"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <flux:input wire:model="discount" :label="__('Discount')" type="number"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                        <div>
                            <flux:input wire:model="discount_price" :label="__('Discount Price')" type="number"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <flux:input wire:model="quantity" :label="__('Quantity')" type="number"
                                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                        </div>
                    </div>
                </div>

                <!-- Images Tab -->
                <div x-show="tab === 'images'" class="p-4">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Images</h2>

                    <input type="file" wire:model="images" multiple
                        class="block w-full p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">

                    @if ($images)
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            @foreach ($images as $image)
                                <img src="{{ $image->temporaryUrl() }}" class="w-full h-32 object-cover rounded-md">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between">
            <div class="flex justify-end">
                <button wire:click="save" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>
