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

    public $name;
    public $code;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:units,code',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the unit name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',
            'code.required' => 'Please enter the unit code.', 
            'code.unique' => 'This code already exists.',  
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
            $this->unit->update([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            flash()->success('Unit updated successfully!');
        } else {
            Unit::create([
                'name' => $this->name,
                'code' => $this->code,
            ]);
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
    }
};

?>

<div>
    <x-units-form 
        :is-editing="false"
    />
</div>