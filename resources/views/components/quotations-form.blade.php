@props([
    'isEditing' => false,
    'customers' => [],
    'products' => [],
    'agents' => [],
])

@php
    $items = $this->items;
@endphp

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit Quotation' : 'Create New Quotation' }}
            </h3>
        </div>
        
        <div class="bg-white dark:bg-gray-900 px-6 pt-6 pb-6 sm:p-8 sm:pb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="mb-4">
                    @if($isEditing)
                        <flux:input wire:model="quotation_number" :label="__('Quotation Number')" type="text"
                            readonly class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    @else
                        <flux:input wire:model="quotation_number" :label="__('Quotation Number')" type="text"
                            readonly class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                    @endif
                </div>
                <div class="mb-4">
                    <flux:select wire:model.live="status" :label="__('Status')" size="md" value="">
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
                                    class="w-2/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Product
                                </th>
                                <th
                                    class="w-1/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Quantity
                                </th>
                                <th
                                    class="w-1/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Unit Price
                                </th>
                                <th
                                    class="w-1/5 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($items as $index => $item)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
                                wire:key="item-{{ $index }}">
                                <td class="w-2/5 px-6 py-4 align-top">
                                    <div class="space-y-1">
                                        <flux:select
                                            wire:model.live.debounce.500ms="items.{{ $index }}.product_id"
                                            name="items.{{ $index }}.product_id"
                                            size="md"
                                            :label="__('')"
                                        >
                                            <flux:select.option value="">Select Product...</flux:select.option>
                                        
                                            @foreach ($products as $product)
                                                <flux:select.option
                                                    value="{{ $product['id'] }}"
                                                >
                                                    {{ $product['name'] }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                </td>
                                <td class="w-1/5 px-6 py-4 align-top">
                                    <div class="space-y-1">
                                        <div class="relative">
                                            <flux:input
                                                type="number"
                                                wire:model.live="items.{{ $index }}.quantity"
                                                placeholder="Qty"
                                                :iconTrailing="false"
                                                min="1"
                                                :label="__('')"
                                            >
                                                <x-slot name="iconTrailing">
                                                    <span class="text-gray-400 text-xs dark:text-zinc-400">units</span>
                                                </x-slot>
                                            </flux:input>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-1/5 px-6 py-4 align-top">
                                    <div class="space-y-1">
                                        <div class="relative">
                                            <flux:input
                                                type="number"
                                                wire:model.live="items.{{ $index }}.unit_price"
                                                placeholder="0.00"
                                                :iconLeading="false"
                                                step="0.01" 
                                                min="0"
                                                :label="__('')"
                                            >
                                                <x-slot name="iconLeading">
                                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                                                </x-slot>
                                            </flux:input>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-1/5 px-6 py-4 align-top">
                                    <div class="space-y-1">
                                        <div class="relative">
                                            <flux:input
                                                type="number"
                                                wire:model="items.{{ $index }}.total_price"
                                                placeholder="0.00"
                                                readonly
                                                :iconLeading="false"
                                            >
                                                <x-slot name="iconLeading">
                                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                                                </x-slot>
                                            </flux:input>
                                        </div>
                                        
                                        @error("items.{$index}.total_price")
                                            <div class="text-(--color-accent-2) text-xs mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @if ($index > 0)
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="invisible group-hover:visible inline-flex items-center justify-center w-8 h-8 rounded-full text-(--color-accent-2) hover:text-white hover:bg-(--color-accent-2) transition-all focus:outline-none focus:ring-2 focus:ring-(--color-accent-2) focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
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
                            class="inline-flex items-center text-sm text-[color:var(--color-accent)] hover:text-[#006499] dark:text-blue-400 dark:hover:text-blue-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                fill="currentColor">
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
                    <flux:select
                        wire:model="agent_id" id="agent_id" :label="__('Agent')"
                    >
                        <flux:select.option value="">Select Agent...</flux:select.option>
                    
                        @foreach ($agents as $agent)
                            <flux:select.option
                                value="{{ $agent->id }}"
                            >
                                {{ $agent->name }}
                            </flux:select.option>svg xml
                        @endforeach
                    </flux:select>
                </div>

                <div class="mb-4">
                    <flux:input
                        type="date"
                        wire:model="valid_until"
                        :label="__('Valid Until')"
                    >
                    </flux:input>   
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="mb-4">
                    <flux:input
                        type="number"
                        wire:model.live="tax"
                        placeholder="0.00"
                        :iconLeading="false"
                        step="0.01" min="0"
                        id="tax"
                        :label="__('Tax (%)')"
                    >
                        <x-slot name="iconTrailing">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">%</span>
                        </x-slot>
                    </flux:input>
                </div>

                <div class="mb-4">
                    <flux:input
                        type="number"
                        wire:model.live="discount"
                        placeholder="0.00"
                        step="0.01" min="0"
                        id="discount"
                        :label="__('Discount')"
                    >
                    </flux:input>
                </div>

                <div class="mb-4">
                    <flux:input
                        type="number"
                        wire:model.live="total_amount"
                        placeholder="0.00"
                        step="0.01" min="0"
                        id="total_amount"
                        :label="__('Total Amount')"
                    >
                        <x-slot name="iconLeading">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">₱</span>
                        </x-slot>
                    </flux:input>
                </div>

                <div class="mb-4 md:col-span-3">
                    <label for="notes"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Notes
                    </label>
                    <textarea wire:model="notes" id="notes" rows="3"
                        class="w-full rounded border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none  dark:border-gray-600"></textarea>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 gap-2 sm:flex sm:flex-row-reverse sm:px-8 rounded-b-lg">
            <flux:button type="submit" variant="primary">{{ $isEditing ? 'Update' : 'Save' }}</flux:button>
            <flux:button variant="danger" wire:click="cancel">Cancel</flux:button>
        </div>
    </form>
</div>