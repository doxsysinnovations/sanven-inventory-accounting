<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $user;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $userToDelete;
    public $roles = [];
    public $selectedRoles = [];
    public $selectedUserType = '';

    public $form = [
        'name' => '',
        'username' => '',
        'email' => '',
        'user_type' => '',
        'password' => '',
    ];

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function rules()
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.email' => $this->isEditing ? 'required|email|unique:users,email,' . $this->user->id : 'required|email|unique:users,email',
            'form.email' => $this->isEditing ? 'required|unique:users,username,' . $this->user->id : 'required|unique:users,username',
            'form.password' => $this->isEditing ? '' : 'required|min:8',
            'form.user_type' => 'required',
            'selectedRoles' => 'array',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->selectedRoles = [];
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(User $user)
    {
        $this->user = $user;
        $this->form = $user->only(['name', 'username', 'email', 'user_type']);
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Convert all role IDs to integers
        $this->selectedRoles = array_map('intval', $this->selectedRoles);

        if ($this->isEditing) {
            $this->user->update($this->form);
            $this->user->syncRoles($this->selectedRoles);
            flash()->success('User updated successfully!');
        } else {
            $user = User::create($this->form);
            $user->assignRole($this->selectedRoles);
            flash()->success('User created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($userId)
    {
        $this->userToDelete = $userId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $user = User::find($this->userToDelete);
        if ($user) {
            $user->roles()->detach();
            $user->delete();
            flash()->success('User deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->userToDelete = null;
    }

    private function resetForm()
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'username' => '',
            'user_type' => '',
            'password' => '',
        ];
        $this->selectedRoles = [];
        $this->user = null;
    }

    #[Title('Users')]
    public function with(): array
    {
        return [
            'users' => $this->users,
        ];
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with('roles')
            ->where('name', 'like', '%' . $this->search . '%')
            ->when($this->selectedUserType, function ($query) {
                return $query->where('user_type', $this->selectedUserType);
            })
            ->paginate(10)
            ->through(function ($user) {
                // Fetch the latest login activity for each user
                $lastLogin = Activity::where('log_name', 'user-login')->where('causer_id', $user->id)->latest()->first();

                $user->last_logged_in = $lastLogin ? $lastLogin->created_at : null;

                return $user;
            });
    }
};

?>

<div>
    <div class="mb-4">
        <nav class="flex justify-end" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Users</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="w-1/3">
                <input wire:model.live="search" type="search" placeholder="Search users..."
                    class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
            </div>
            <flux:radio.group wire:model.live="selectedUserType" variant="segmented" size="md">
                <flux:radio value="" label="All" />
                <flux:radio value="admin" label="Admin" />
                <flux:radio value="staff" label="Staff" />
                <flux:radio value="agent" label="Agent" />
            </flux:radio.group>
        </div>
        @if ($users->isEmpty())
            <div class="flex flex-col items-center justify-center p-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-48 h-48 mb-4 text-gray-300 dark:text-gray-600"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mb-4 text-gray-500 dark:text-gray-400">No users found</p>
                @can('users.create')
                    <button wire:click="create"
                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white transition-all duration-200 ease-in-out hover:bg-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:bg-green-800 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="my-auto mr-2 h-5 w-5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add User
                    </button>
                @endcan
            </div>
        @else
            <div class="flex justify-end">
                @can('users.create')
                    <button wire:click="create"
                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 dark:bg-green-500 dark:hover:bg-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add User
                    </button>
                @endcan

            </div>
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Name</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Username</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Email</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Verified at</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                User Type</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Roles</th>
                            @can('users.disable-enable')
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status</th>
                            @endcan

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Last Login</th>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($users as $user)
                            @if (auth()->user()->user_type === 'superadmin' || $user->user_type !== 'superadmin')
                                <tr class="dark:hover:bg-gray-800" wire:key="user-{{ $user->id ?? uniqid() }}">
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">{{ $user->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">{{ $user->username }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">{{ $user->email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                        {{ $user->email_verified_at }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                        @switch($user->user_type)
                                            @case('student')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">
                                                    Student
                                                </span>
                                            @break

                                            @case('admin')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-900 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:text-purple-300">
                                                    Admin
                                                </span>
                                            @break

                                            @case('superadmin')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">
                                                    Super Admin
                                                </span>
                                            @break

                                            @case('faculty')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:text-blue-300">
                                                    Faculty
                                                </span>
                                            @break

                                            @default
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-900 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:text-gray-300">
                                                    {{ $user->user_type }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                        @foreach ($user->roles as $role)
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:text-blue-300">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    @can('users.disable-enable')
                                        <td class="text-center whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                            @if ($user->is_active)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">
                                                    Active
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">
                                                    Disabled
                                                </span>
                                            @endif
                                        </td>
                                    @endcan
                                    <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                        @if ($user->last_logged_in)
                                            {{ $user->last_logged_in->diffForHumans() }}
                                        @else
                                            Never logged in
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 space-x-2">
                                        @can('users.edit')
                                            <button wire:click="edit({{ $user->id }})"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>
                                        @endcan
                                        @can('users.delete')
                                            <button wire:click="confirmDelete({{ $user->id }})"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                        @endcan
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    @if ($isEditing)
                        <div class="flex justify-between p-4">
                            <h4>Edit this</h4>
                            @can('users.disable-enable')
                                <livewire:widget.active-status-change :model="$user" :field="'is_active'"
                                    :wire:key="'status-' . ($user->id ?? uniqid())" />
                            @endcan
                        </div>
                    @endif
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

                            <div class="mb-4">
                                <flux:input wire:model="form.name" :label="__('Name')" type="text"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                                @error('form.name')
                                    <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="form.username" :label="__('Username')" type="text"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                                @error('form.username')
                                    <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="form.email" :label="__('Email')" type="email"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                                @error('form.email')
                                    <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <flux:select wire:model.live="form.user_type" :label="__('User Type')">
                                    <flux:select.option value="">Choose</flux:select.option>
                                    <flux:select.option value="admin">Admin</flux:select.option>
                                    <flux:select.option value="faculty">Faculty</flux:select.option>
                                    <flux:select.option value="student">Student</flux:select.option>
                                    @if (auth()->user()->user_type === 'superadmin')
                                        <flux:select.option value="superadmin">Super Admin</flux:select.option>
                                    @endif
                                </flux:select>
                                @error('form.user_type')
                                    <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="form.password" :label="__('Password')" type="password"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                                @error('form.password')
                                    <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Roles</label>
                                <div class="mt-2 space-y-2">
                                    @foreach ($roles as $role)
                                        @if (auth()->user()->user_type === 'superadmin')
                                            <div class="flex items-center">
                                                <input type="checkbox" wire:model="selectedRoles"
                                                    value="{{ $role->id }}"
                                                    class="h-6 w-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
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
                                                    class="h-6 w-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                                    id="role-{{ $role->id }}">
                                                <label for="role-{{ $role->id }}"
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @error('selectedRoles')
                                    <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-400 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="$set('showModal', false)"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Delete User
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Are you sure you want to delete this user? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="delete"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-400 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="$set('confirmingDelete', false)"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
