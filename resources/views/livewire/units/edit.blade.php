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

    public $code;
    public $name;
    public $description;

    public function mount(Unit $unit)
    {
        $this->isEditing = true;
        $this->resetValidation();
        $this->unit = $unit;
        $this->code = $unit->code;
        $this->name = $unit->name;
        $this->description = $unit->description;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the unit name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->unit->update(['name' => $this->name, 'description' => $this->description]);
            flash()->success('Unit updated successfully!');
        } else {
            Unit::create(['name' => $this->name]);
            flash()->success('Unit created successfully!');
        }

        return redirect()->route('units');
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
            ->paginate(10);
    }

    public function cancel()
    {
        $this->resetForm();
        return redirect()->route('units');
    }
};

?>

<div>
    <x-units-form
        :is-editing="true"
    />
</div>
