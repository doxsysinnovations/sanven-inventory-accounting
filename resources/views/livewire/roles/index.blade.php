<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $role;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $roleToDelete;
    public $permissions = [];
    public $selectedPermissions = [];
    public $selectAll = false;
    public $perPage = 10;

    public $name;

    public function mount()
    {
        $this->permissions = Permission::all();
        $this->perPage = session('perPage', 10);
    }

    public function updatedPerPage($value)
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'selectedPermissions' => 'required|array',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Role $role)
    {
        $this->resetValidation();
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Convert all permission IDs to integers
        $this->selectedPermissions = array_map('intval', $this->selectedPermissions);

        if ($this->isEditing) {
            try {
                $this->role->update(['name' => $this->name]);
                $this->role->syncPermissions($this->selectedPermissions);
                flash()->success('Role updated successfully!');
            } catch (\Exception $e) {
                flash()->error('Error updating role: ' . $e->getMessage());
            }
        } else {
            $role = Role::create(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);
            flash()->success('Role created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }
    public function confirmDelete($roleId)
    {
        $this->roleToDelete = $roleId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $role = Role::find($this->roleToDelete);
        if ($role) {
            $role->delete();
            flash()->success('Role deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->roleToDelete = null;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPermissions = $this->permissions->pluck('id')->toArray();
        } else {
            $this->selectedPermissions = [];
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->selectedPermissions = [];
        $this->role = null;
        $this->selectAll = false;
        $this->resetValidation();
    }

    #[Title('Roles')]
    public function with(): array
    {
        return [
            'roles' => Role::query()
                ->where('name', 'like', '%' . $this->search . '%')
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div>
        <x-view-layout
            title="Roles"
            :items="$roles"
            searchPlaceholder="Search Roles..."
            message="No roles found."
            :perPage="$perPage"
            createButtonLabel="Add Role"
            createButtonAbility="roles.create"
            createButtonRoute="roles.create"
        >
        <x-slot:emptyIcon>
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M11.644 3.066a1 1 0 0 1 .712 0l7 2.666A1 1 0 0 1 20 6.68a17.694 17.694 0 0 1-2.023 7.98 17.406 17.406 0 0 1-5.402 6.158 1 1 0 0 1-1.15 0 17.405 17.405 0 0 1-5.403-6.157A17.695 17.695 0 0 1 4 6.68a1 1 0 0 1 .644-.949l7-2.666Zm4.014 7.187a1 1 0 0 0-1.316-1.506l-3.296 2.884-.839-.838a1 1 0 0 0-1.414 1.414l1.5 1.5a1 1 0 0 0 1.366.046l4-3.5Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
            <x-list-table
                :headers="['name', 'Permissions', 'Actions']"
                :rows="$roles->map(fn($role) => [
                    $role->name,
                    $role->permissions
                        ->map(fn($permission) => 
                            '<span class=\'inline-flex items-center rounded-full bg-(--color-accent-muted) dark:bg-blue-900 px-2.5 py-0.5 text-xs font-medium text-(--color-accent) dark:text-blue-300\'>'
                            . e($permission->name) .
                            '</span>'
                        )
                        ->implode(' '),
                    'actions-placeholder',
                    '__model' => $role
                ])"
                editAbility="roles.edit"
                editParameter="role"
                editRoute="roles.edit"
                deleteAbility="roles.delete"
                deleteAction="confirmDelete"
            />
        </x-view-layout>

        @if ($confirmingDelete)
            <x-delete-modal 
                title="Delete Role"
                message="Are you sure you want to delete this role? This action cannot be undone."
                onCancel="$set('confirmingDelete', false)"
            />
        @endif
    </div>
</div>