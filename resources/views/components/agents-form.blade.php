@props([
    'isEditing' => false,
    'locations',
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit Agent Profile' : 'Add New Agent' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="mb-4">
                <flux:input wire:model="name" :label="__('Name')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="email" :label="__('Email')" type="email"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="phone" :label="__('Phone')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <flux:input wire:model="address" :label="__('Address')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>

            <div class="mb-4">
                <label for="locations" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    Location/s
                </label>

                <div class="relative" x-data="{ open: false }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-left text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500"
                    >
                        <template x-if="$wire.selectedLocations.length">
                            <div class="flex flex-wrap gap-1">
                                <template x-for="id in $wire.selectedLocations" :key="id">
                                    <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 px-2 py-1 rounded text-xs">
                                        <span class="font-medium" x-text="$wire.allLocations[id]"></span>
                                    </span>
                                </template>
                            </div>
                        </template>

                        <span x-show="!$wire.selectedLocations.length" class="text-gray-400">Select locations</span>
                    </button>

                    <div
                        x-show="open"
                        @click.away="open = false"
                        class="absolute z-50 mt-1 w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg max-h-60 overflow-y-auto"
                    >
                        <ul class="p-2 space-y-1 text-sm">
                            @foreach($locations as $id => $name)
                                <li>
                                    <label class="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            value="{{ $id }}"
                                            wire:model.live="selectedLocations"
                                            class="accent-(--color-accent-alt) text-blue-600 border-gray-300 rounded shadow-sm focus:ring-blue-500"
                                        />
                                        <span class="text-gray-900 dark:text-gray-100">{{ $name }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @error('selectedLocations')
                    <span class="text-(--color-accent-2) text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input class="accent-(--color-accent-alt) form-checkbox" type="checkbox" wire:model="is_active">
                    <span class="ml-2">Active</span>
                </label>
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