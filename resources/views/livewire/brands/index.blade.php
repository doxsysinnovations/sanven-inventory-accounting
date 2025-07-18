<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $brand;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $brandToDelete;
    public $name = '';
    public $slug = '';
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
            'name' => 'required|string|max:255',
            'slug' => $this->isEditing ? 'required|string|unique:brands,slug,' . $this->brand->id : 'required|string|unique:brands,slug',
        ];
    }

    public function confirmDelete($id)
    {
        $this->brandToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $brand = Brand::find($this->brandToDelete);
        if ($brand) {
            $brand->delete();
            flash()->success('Brand deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->brandToDelete = null;
    }

    #[Title('Brands')]
    public function with(): array
    {
        return [
            'brands' => $this->brands,
        ];
    }

    public function getBrandsProperty()
    {
        return Brand::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }
};

?>
<div>
    <x-view-layout
        title="All Brands"
        :items="$brands"
        searchPlaceholder="Search Brands..."
        message="No brands available."
        :perPage="$perPage"
        createButtonLabel="Add Brand"
        createButtonAbility="brands.create"
        createButtonRoute="brands.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M11.906 1.994a8.002 8.002 0 0 1 8.09 8.421 7.996 7.996 0 0 1-1.297 3.957.996.996 0 0 1-.133.204l-.108.129c-.178.243-.37.477-.573.699l-5.112 6.224a1 1 0 0 1-1.545 0L5.982 15.26l-.002-.002a18.146 18.146 0 0 1-.309-.38l-.133-.163a.999.999 0 0 1-.13-.202 7.995 7.995 0 0 1 6.498-12.518ZM15 9.997a3 3 0 1 1-5.999 0 3 3 0 0 1 5.999 0Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Slug', 'Actions']"
            :rows="$brands->map(fn($brand) => [
                $brand->name,
                $brand->slug,
                'actions-placeholder',
                '__model' => $brand
            ])"
            editAbility="brands.edit"
            editParameter="brand"
            editRoute="brands.edit"
            deleteAbility="brands.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Brand"
            message="Are you sure you want to delete this brand? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>