@props([
    'isEditing' => false,
])

<div>
    <form wire:submit="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit Brand' : 'Add New Brand' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="mb-4">
                <flux:input wire:model.live="name" :label="__('Name')" type="text" 
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="slug" :label="__('Slug')" type="text" readonly
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:justify-end sm:space-x-2 space-y-2 sm:space-y-0 flex flex-col sm:flex-row">
            <flux:button class="sm:w-auto" variant="danger" wire:click="cancel">Cancel</flux:button>
            <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit" >
                {{ $isEditing ? 'Update' : 'Create' }}
            </flux:button>
        </div>
    </form>
</div>