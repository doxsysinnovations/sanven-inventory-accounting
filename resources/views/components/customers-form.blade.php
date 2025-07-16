@props([
    'isEditing' => false,
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit Customer' : 'Create New Customer' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="space-y-4">
                <div>
                    <flux:input wire:model="name" :label="__('Name')" type="text" 
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <flux:input wire:model="phone" :label="__('Phone')" type="tel"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
                <div>
                    <flux:input wire:model="address" :label="__('Address')" type="text"
                        class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-800 gap-2 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <flux:button type="submit" variant="primary">{{ $isEditing ? 'Update' : 'Create' }}</flux:button>
            <flux:button variant="danger" wire:click="cancel">Cancel</flux:button>
        </div>

    </form>
</div>