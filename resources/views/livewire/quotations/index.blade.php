<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Product;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $quotation;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $quotationToDelete;

    // Form fields
    public $quotation_number = '';
    public $customer_id = null;
    public $agent_id = null;
    public $total_amount = 0;
    public $tax = 0;
    public $discount = 0;
    public $notes = '';
    public $status = 'draft';
    public $valid_until = '';

    // Items fields
    public $items = [];
    public $products = [];

    public $customers = [];
    public $agents = [];

    public function mount()
    {
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'total_price' => 0,
            'description' => '',
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function updatedItems($value, $key)
    {
        $index = explode('.', $key)[0];
        $field = explode('.', $key)[1];

        if ($field === 'product_id') {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = $product->price;
                $this->items[$index]['description'] = $product->description;
            }
        }

        if ($field === 'quantity' || $field === 'unit_price') {
            $this->items[$index]['total_price'] = $this->items[$index]['quantity'] * $this->items[$index]['unit_price'];
        }

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_amount = collect($this->items)->sum('total_price');
        $this->total_amount = $this->total_amount + $this->total_amount * ($this->tax / 100) - $this->discount;
    }

    public function rules()
    {
        return [
            'quotation_number' => $this->isEditing ? 'required|string|unique:quotations,quotation_number,' . $this->quotation->id : 'required|string|unique:quotations,quotation_number',
            'customer_id' => 'nullable|exists:customers,id',
            'agent_id' => 'nullable|exists:agents,id',
            'total_amount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'valid_until' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ];
    }

    public function generateQuotationNumber()
    {
        $this->quotation_number = 'QUO-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->generateQuotationNumber();
        $this->showModal = true;
    }

    public function edit(Quotation $quotation)
    {
        $this->resetValidation();
        $this->quotation = $quotation;
        $this->quotation_number = $quotation->quotation_number;
        $this->customer_id = $quotation->customer_id;
        $this->agent_id = $quotation->agent_id;
        $this->total_amount = $quotation->total_amount;
        $this->tax = $quotation->tax;
        $this->discount = $quotation->discount;
        $this->notes = $quotation->notes;
        $this->status = $quotation->status;
        $this->valid_until = $quotation->valid_until->format('Y-m-d');
        $this->items = $quotation->items
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'description' => $item->description,
                ];
            })
            ->toArray();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete($quotationId)
    {
        $this->quotationToDelete = $quotationId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $quotation = Quotation::find($this->quotationToDelete);
        if ($quotation) {
            $quotation->delete();
            flash()->success('Quotation deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->quotationToDelete = null;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'quotation_number' => $this->quotation_number,
            'customer_id' => $this->customer_id,
            'agent_id' => $this->agent_id,
            'total_amount' => $this->total_amount,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'notes' => $this->notes,
            'status' => $this->status,
            'valid_until' => $this->valid_until,
        ];

        if ($this->isEditing) {
            $this->quotation->update($data);
            $this->quotation->items()->delete();
            foreach ($this->items as $item) {
                $this->quotation->items()->create($item);
            }
            flash()->success('Quotation updated successfully!');
        } else {
            $quotation = Quotation::create($data);
            foreach ($this->items as $item) {
                $quotation->items()->create($item);
            }
            flash()->success('Quotation created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['quotation_number', 'customer_id', 'agent_id', 'total_amount', 'tax', 'discount', 'notes', 'status', 'valid_until', 'quotation', 'items']);
        $this->resetValidation();
        $this->addItem();
    }

    #[Title('Quotations')]
    public function with(): array
    {
        return [
            'quotations' => $this->quotations,
        ];
    }

    public function getQuotationsProperty()
    {
        return Quotation::query()
            ->with(['customer', 'agent'])
            ->where(function ($query) {
                $query->where('quotation_number', 'like', '%' . $this->search . '%')->orWhereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
};
?>

<div>
    <div class="mb-4">
        <nav class="flex justify-end" aria-label="Breadcrumb">
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
                        <span
                            class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Quotations</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="w-1/3">
                <input wire:model.live="search" type="search" placeholder="Search quotations..."
                    class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
            </div>
            @can('quotations.create')
                <button wire:click="create"
                    class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 dark:bg-green-500 dark:hover:bg-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Create Quotation
                </button>
            @endcan
        </div>

        @if ($quotations->isEmpty())
            <div class="flex flex-col items-center justify-center p-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-48 h-48 mb-4 text-gray-300 dark:text-gray-600"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mb-4 text-gray-500 dark:text-gray-400">No quotations found</p>
            </div>
        @else
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Quotation #
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Customer
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Amount
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Valid Until
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($quotations as $quotation)
                            <tr class="dark:hover:bg-gray-800" wire:key="quotation-{{ $quotation->id }}">
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ $quotation->quotation_number }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ $quotation->customer?->name ?? 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ number_format($quotation->total_amount, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php
                                        $statusClasses = [
                                            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                            'accepted' =>
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                        ];
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$quotation->status] }}">
                                        {{ ucfirst($quotation->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ $quotation->valid_until->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 space-x-2">
                                    @can('quotations.edit')
                                        <button wire:click="edit({{ $quotation->id }})"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>
                                    @endcan
                                    @can('quotations.delete')
                                        <button wire:click="confirmDelete({{ $quotation->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $quotations->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl sm:align-middle">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-900 px-6 pt-6 pb-6 sm:p-8 sm:pb-8">
                            <h3 class="text-xl font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                                {{ $isEditing ? 'Edit Quotation' : 'Create New Quotation' }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="mb-4">
                                    <flux:input wire:model="quotation_number" :label="__('Quotation Number')"
                                        type="text"
                                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                                </div>

                                <div class="mb-4">
                                    <flux:select wire:model.live="status" :label="__('Status')" size="md">
                                        <flux:select.option value="">Choose Status...</flux:select.option>
                                        <flux:select.option value="draft">Draft</flux:select.option>
                                        <flux:select.option value="sent">Sent</flux:select.option>
                                        <flux:select.option value="accepted">Accepted</flux:select.option>
                                        <flux:select.option value="rejected">Rejected</flux:select.option>
                                    </flux:select>
                                </div>
                                <div class="mb-4">
                                    <flux:select wire:model.live="customer_id" :label="__('Customer')" size="md">
                                        <flux:select.option value="">Choose customer...</flux:select.option>
                                        @foreach ($customers as $customer)
                                            <flux:select.option value="{{ $customer->id }}">{{ $customer->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>

                                </div>
                            </div>
                            <div class="mt-4 mb-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Items</h4>
                                <div class="space-y-6">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    Product
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    Quantity
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    Unit Price
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    Total
                                                </th>
                                                <th class="px-6 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($items as $index => $item)
                                                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
                                                    wire:key="item-{{ $index }}">
                                                    <td class="px-6 py-4">
                                                        <select wire:model="items.{{ $index }}.product_id"
                                                            class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-3.5 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                                            <option value="">Select Product</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product->id }}">
                                                                    {{ $product->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="relative">
                                                            <input wire:model="items.{{ $index }}.quantity"
                                                                type="number" min="1" placeholder="Qty"
                                                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 pl-3 pr-12 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                                            <span
                                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-medium">
                                                                units
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="relative">
                                                            <span
                                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">$</span>
                                                            <input wire:model="items.{{ $index }}.unit_price"
                                                                type="number" step="0.01" min="0"
                                                                placeholder="0.00"
                                                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 pl-8 pr-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="relative">
                                                            <span
                                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">$</span>
                                                            <input wire:model="items.{{ $index }}.total_price"
                                                                type="number" step="0.01" min="0" readonly
                                                                placeholder="0.00"
                                                                class="w-full rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 pl-8 pr-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-0 cursor-not-allowed">
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if ($index > 0)
                                                            <button type="button"
                                                                wire:click="removeItem({{ $index }})"
                                                                class="invisible group-hover:visible inline-flex items-center justify-center w-8 h-8 rounded-full text-red-600 hover:text-white hover:bg-red-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-5 w-5" viewBox="0 0 20 20"
                                                                    fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="mt-4">
                                        <button type="button" wire:click="addItem"
                                            class="inline-flex items-center text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Add Item
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                <div class="mb-4">
                                    <label for="agent_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Agent
                                    </label>
                                    <select wire:model="agent_id" id="agent_id"
                                        class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                        <option value="">Select Agent</option>
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="total_amount"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Total Amount
                                    </label>
                                    <input wire:model="total_amount" id="total_amount" type="number" step="0.01"
                                        class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                </div>

                                <div class="mb-4">
                                    <label for="tax"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tax (%)
                                    </label>
                                    <input wire:model="tax" id="tax" type="number" step="0.01"
                                        class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                </div>

                                <div class="mb-4">
                                    <label for="discount"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Discount
                                    </label>
                                    <input wire:model="discount" id="discount" type="number" step="0.01"
                                        class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                </div>

                                <div class="mb-4">
                                    <label for="valid_until"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Valid Until
                                    </label>
                                    <input wire:model="valid_until" id="valid_until" type="date"
                                        class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
                                </div>

                                <div class="mb-4 md:col-span-3">
                                    <label for="notes"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Notes
                                    </label>
                                    <textarea wire:model="notes" id="notes" rows="3"
                                        class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600"></textarea>
                                </div>
                            </div>


                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8">
                            <flux:button type="submit" class="sm:ml-3 sm:w-auto sm:text-sm" variant="primary">
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </flux:button>
                            <button type="button" wire:click="$set('showModal', false)"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-2.5 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Delete Quotation
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Are you sure you want to delete this quotation? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="delete"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-400 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="$set('confirmingDelete', false)"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
