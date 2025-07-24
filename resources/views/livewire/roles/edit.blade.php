<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $role;
    public $isEditing = false;
    public $permissions = [];
    public $selectedPermissions = [];
    public $selectAll = false;

    public $name;

    public function mount(Role $role)
    {
        $this->permissions = Permission::all();
        $this->isEditing = true;
        $this->resetValidation();
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        
        $allPermissionIds = $this->permissions->pluck('id')->toArray();
        $this->selectAll = count($this->selectedPermissions) === count($allPermissionIds) && 
                        empty(array_diff($allPermissionIds, $this->selectedPermissions));
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'selectedPermissions' => 'required|array',
        ];
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

        return redirect()->route('roles');
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

    public function cancel() 
    {
        if($this->isEditing) {
            return redirect()->route('roles');
        }   
    }
};
?>

<div>
    <x-roles-form
        :is-editing="true"
        :permissions="$permissions"
    />
</div>