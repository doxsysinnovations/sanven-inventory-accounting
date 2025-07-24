@props([
    'isEditing' => false,
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit Supplier Profile' : 'Add New Supplier' }}
            </h3>
        </div>
        
        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

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
        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 gap-2 sm:flex sm:flex-row-reverse sm:px-8 rounded-b-lg">
            <flux:button type="submit" variant="primary">{{ $isEditing ? 'Update' : 'Save' }}</flux:button>
            <flux:button variant="danger" wire:click="cancel">Cancel</flux:button>
        </div>
    </form>
</div>