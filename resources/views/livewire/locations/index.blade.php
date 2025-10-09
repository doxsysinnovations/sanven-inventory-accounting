<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Location;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $location;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $locationToDelete;
    public $name = '';
    public $is_active = true;
    public $perPage = 5;

    public function mount() {
        $this->perPage = session('perPage', 5);
    }

    public function updatedPerPage($value) 
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => $this->isEditing
                ? 'required|string|max:255|unique:locations,name,' . $this->location->id
                : 'required|string|max:255|unique:locations,name',
            'is_active' => 'boolean'
        ];
    }

    public function confirmDelete($locationId)
    {
        $this->locationToDelete = $locationId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $location = Location::find($this->locationToDelete);
        if ($location) {
            $location->delete();
            flash()->success('Location deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->locationToDelete = null;
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
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="All Locations"
        :items="$locations"
        searchPlaceholder="Search Locations..."
        message="No locations found."
        :perPage="$perPage"
        createButtonLabel="Add Location"
        createButtonAbility="locations.create"
        createButtonRoute="locations.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M11.906 1.994a8.002 8.002 0 0 1 8.09 8.421 7.996 7.996 0 0 1-1.297 3.957.996.996 0 0 1-.133.204l-.108.129c-.178.243-.37.477-.573.699l-5.112 6.224a1 1 0 0 1-1.545 0L5.982 15.26l-.002-.002a18.146 18.146 0 0 1-.309-.38l-.133-.163a.999.999 0 0 1-.13-.202 7.995 7.995 0 0 1 6.498-12.518ZM15 9.997a3 3 0 1 1-5.999 0 3 3 0 0 1 5.999 0Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Status', 'Actions']"
            :rows="$locations->map(fn($location) => [
                $location->name,
                $location->is_active ? 'active' : 'inactive',
                'actions-placeholder',
                '__model' => $location
            ])"
            editAbility="locations.edit"
            editParameter="location"
            editRoute="locations.edit"
            deleteAbility="locations.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Location"
            message="Are you sure you want to delete this location? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>