@props([
    'isEditing' => false,
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-gray-100">
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
        <div class="bg-gray-50 rounded-b-lg dark:bg-gray-800 px-4 py-6 sm:px-6 sm:flex sm:justify-end sm:space-x-2 space-y-2 sm:space-y-0 flex flex-col sm:flex-row">
            <flux:button class="sm:w-auto" variant="danger" wire:click="cancel">Cancel</flux:button>
            <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit" >
                {{ $isEditing ? 'Update' : 'Create' }}
            </flux:button>
        </div>
    </form>
</div>