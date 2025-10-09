@props([
    'isEditing' => false,
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                {{ $isEditing ? 'Edit Customer Profile' : 'Add New Customer' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-(--color-accent-dark) p-8 sm:p-10">
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
                <div>
                    <flux:select wire:model.live="type" :label="__('Customer Type')" size="md" searchable>
                        <flux:select.option value="">Choose Customer type...</flux:select.option>
                        <flux:select.option value="Private">Private</flux:select.option>
                        <flux:select.option value="Government">Government</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        <div
            class="bg-gray-50 rounded-b-lg dark:bg-(--color-accent-4-dark) p-8 sm:px-6 sm:flex sm:justify-end sm:space-x-2 space-y-2 sm:space-y-0 flex flex-col sm:flex-row">
            <flux:button class="sm:w-auto" variant="danger" wire:click="cancel">Cancel</flux:button>
            <flux:button class="sm:w-auto" variant="primary" color="blue" type="submit">
                {{ $isEditing ? 'Update' : 'Create' }}
            </flux:button>
        </div>
    </form>
</div>
