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
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.045 3.007 12.31 3a1.965 1.965 0 0 0-1.4.585l-7.33 7.394a2 2 0 0 0 0 2.805l6.573 6.631a1.957 1.957 0 0 0 1.4.585 1.965 1.965 0 0 0 1.4-.585l7.409-7.477A2 2 0 0 0 21 11.479v-5.5a2.972 2.972 0 0 0-2.955-2.972Zm-2.452 6.438a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/>
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