@props([
    'isEditing' => false,
])



<div>
    <div class="p-4 bg-gray-100 rounded-lg dark:bg-gray-800 mb-6">
        <h2 class="font-bold text-lg mb-6 text-gray-700 dark:text-gray-200">Bulk Import Suppliers</h2>
        <div class="flex flex-col sm:flex-row gap-4">

            <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="block text-sm text-gray-700 dark:text-gray-300">

            <button 
                wire:click="importSuppliers"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow">
                Import Suppliers
            </button>

            <button 
                wire:click="downloadTemplate"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow">
                Download Template
            </button>

            <button 
                wire:click="exportSuppliers"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow">
                Export Suppliers
            </button>
            
        </div>

        @error('importFile')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                {{ $isEditing ? 'Edit Supplier Profile' : 'Add New Supplier' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-(--color-accent-dark) p-8 sm:p-10">
            <div class="mb-4">
                <flux:input wire:model="name" :label="__('Name')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="trade_name" :label="__('Trade Name')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="identification_number" :label="__('Identification Number')"
                    type="text" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="contact_number" :label="__('Contact Number')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="email" :label="__('Email')" type="email"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:textarea wire:model="address" :label="__('Address')"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
        </div>
        <div class="bg-gray-50 rounded-b-lg dark:bg-(--color-accent-4-dark) p-8 sm:px-6 sm:flex sm:justify-end sm:space-x-2 space-y-2 sm:space-y-0 flex flex-col sm:flex-row">
            <flux:button class="sm:w-auto" variant="danger" wire:click="cancel">Cancel</flux:button>
            <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit" >
                {{ $isEditing ? 'Update' : 'Create' }}
            </flux:button>
        </div>
    </form>
</div>