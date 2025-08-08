@props([
    'isEditing' => false,
    'permissions' => ''
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg dark:bg-(--color-accent-4-dark)">
            <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">
                {{ $isEditing ? 'Edit Role' : 'Add New Role' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-(--color-accent-dark) p-8 sm:p-10">
            <div class="mb-4">
                <flux:input wire:model="name" :label="__('Name')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>
            <div class="mb-4">
                <label class="block text-md font-bold text-gray-900 dark:text-gray-300 mb-2">
                    Permissions
                </label>
                @error('selectedPermissions')
                    <span class="text-(--color-accent-2) dark:text-red-400 text-xs">{{ $message }}</span>
                @enderror
                <div class="flex items-center mb-4">
                    <flux:switch wire:model.live="selectAll"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700" />
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Grant all permissions.</span>
                </div>
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @php
                        $groupedPermissions = $permissions->groupBy(function ($permission) {
                            return explode('.', $permission->name)[0];
                        });
                    @endphp

                    @foreach ($groupedPermissions as $group => $permissions)
                        <div class="space-y-2">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 capitalize">
                                {{ $group }}</h3>
                            @foreach ($permissions as $permission)
                                <div class="flex flex-row-reverse items-center justify-end gap-2">
                                    <span
                                        class="text-sm text-gray-700 dark:text-gray-300">{{ str_replace($group . '.', '', $permission->name) }}</span>
                                    <flux:switch wire:model.live="selectedPermissions"
                                        value="{{ $permission->id }}"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700" />
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
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