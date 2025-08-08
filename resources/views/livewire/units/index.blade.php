<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Unit;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $unit;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $unitToDelete;
    public $perPage = 10;

    public $name;

    public function mount() 
    {
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
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Unit $unit)
    {
        $this->resetValidation();
        $this->unit = $unit;
        $this->name = $unit->name;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->unit->update(['name' => $this->name]);
            flash()->success('Unit updated successfully!');
        } else {
            Unit::create(['name' => $this->name]);
            flash()->success('Unit created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($unitId)
    {
        $this->unitToDelete = $unitId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $unit = Unit::find($this->unitToDelete);
        if ($unit) {
            $unit->delete();
            flash()->success('Unit deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->unitToDelete = null;
    }

    private function resetForm()
    {
        $this->name = '';
        $this->unit = null;
    }

    #[Title('Units')]
    public function with(): array
    {
        return [
            'units' => $this->units,
        ];
    }

    public function getUnitsProperty()
    {
        return Unit::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="All Units"
        :items="$units"
        searchPlaceholder="Search Units..."
        message="No units available."
        :perPage="$perPage"
        createButtonLabel="Add Unit"
        createButtonAbility="units.create"
        createButtonRoute="units.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 4a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm-2.952.462c-.483.19-.868.432-1.19.71-.363.315-.638.677-.831.93l-.106.14c-.21.268-.36.418-.574.527C6.125 6.883 5.74 7 5 7a1 1 0 0 0 0 2c.364 0 .696-.022 1-.067v.41l-1.864 4.2a1.774 1.774 0 0 0 .821 2.255c.255.133.538.202.825.202h2.436a1.786 1.786 0 0 0 1.768-1.558 1.774 1.774 0 0 0-.122-.899L8 9.343V8.028c.2-.188.36-.38.495-.553.062-.079.118-.15.168-.217.185-.24.311-.406.503-.571a1.89 1.89 0 0 1 .24-.177A3.01 3.01 0 0 0 11 7.829V20H5.5a1 1 0 1 0 0 2h13a1 1 0 1 0 0-2H13V7.83a3.01 3.01 0 0 0 1.63-1.387c.206.091.373.19.514.29.31.219.532.465.811.78l.025.027.02.023v1.78l-1.864 4.2a1.774 1.774 0 0 0 .821 2.255c.255.133.538.202.825.202h2.436a1.785 1.785 0 0 0 1.768-1.558 1.773 1.773 0 0 0-.122-.899L18 9.343v-.452c.302.072.633.109 1 .109a1 1 0 1 0 0-2c-.48 0-.731-.098-.899-.2-.2-.12-.363-.293-.651-.617l-.024-.026c-.267-.3-.622-.7-1.127-1.057a5.152 5.152 0 0 0-1.355-.678 3.001 3.001 0 0 0-5.896.04Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Code', 'Name', 'Actions']"
            :rows="$units->map(fn($unit) => [
                $unit->code,
                $unit->name,
                'actions-placeholder',
                '__model' => $unit
            ])"
            editAbility="units.edit"
            editParameter="unit"
            editRoute="units.edit"
            deleteAbility="units.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Unit"
            message="Are you sure you want to delete this unit? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>