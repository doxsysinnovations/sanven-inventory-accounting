@props([
    'isEditing' => false,
    'roles' => []
])

<div>
    <form wire:submit.prevent="save">
        <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $isEditing ? 'Edit User' : 'Add New User' }}
            </h3>
        </div>
        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="mb-4">
                <flux:input wire:model="form.name" :label="__('Name')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>

            <div class="mb-4">
                <flux:input wire:model="form.username" :label="__('Username')" type="text"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>

            <div class="mb-4">
                <flux:input wire:model="form.email" :label="__('Email')" type="email"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>

            <div class="mb-4">
                <flux:select wire:model.live="form.user_type" :label="__('User Type')">
                    <flux:select.option value="">Choose</flux:select.option>
                    <flux:select.option value="admin">Admin</flux:select.option>
                    <flux:select.option value="staff">Staff</flux:select.option>
                    <flux:select.option value="agent">Agent</flux:select.option>
                    @if (auth()->user()->user_type === 'superadmin')
                        <flux:select.option value="superadmin">Super Admin</flux:select.option>
                    @endif
                </flux:select>
            </div>

            <div class="mb-4">
                <flux:input wire:model="form.password" :label="__('Password')" type="password"
                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Roles</label>
                <div class="mt-2 space-y-2">
                    @foreach ($roles as $role)
                        @if (auth()->user()->user_type === 'superadmin')
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="selectedRoles"
                                    value="{{ $role->id }}"
                                    class="h-6 w-6 accent-(--color-accent-alt) text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                    id="role-{{ $role->id }}">
                                <label for="role-{{ $role->id }}"
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @elseif ($role->name !== 'superadmin')
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="selectedRoles"
                                    value="{{ $role->id }}"
                                    class="h-6 w-6 accent-(--color-accent-alt) text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                    id="role-{{ $role->id }}">
                                <label for="role-{{ $role->id }}"
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endif
                    @endforeach
                </div>
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