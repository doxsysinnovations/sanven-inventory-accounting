<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Location;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $location;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $locationToDelete;
    public $name = '';
    public $is_active = true;

    public function rules()
    {
        return [
            'name' => $this->isEditing
                ? 'required|string|max:255|unique:locations,name,' . $this->location->id
                : 'required|string|max:255|unique:locations,name',
            'is_active' => 'boolean'
        ];
    }

    public function messages() 
    {
        return [
            'name.required' => 'Please enter the location’s name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'The name is too long. Please shorten it.',
            'name.unique' => 'This name is already taken. Please choose a different one.',
            'is_active.boolean' => 'Please choose if the location is active or not.',
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
            $this->location->update([
                'name' => $this->name,
                'is_active' => $this->is_active
            ]);
            flash()->success('Location updated successfully!');
        } else {
            Location::create([
                'name' => $this->name,
                'is_active' => $this->is_active
            ]);
            flash()->success('Location created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->is_active = true;
        $this->location = null;
        $this->resetValidation();
    }

    #[Title('Locations')]
    public function with(): array
    {
        return [
            'locations' => $this->locations,
        ];
    }

    public function getLocationsProperty()
    {
        return Location::query()
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
    <x-locations-form 
        :is-editing="false" 
    />
</div>