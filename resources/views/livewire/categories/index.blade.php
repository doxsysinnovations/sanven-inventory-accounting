<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Category;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $category;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $categoryToDelete;
    public $name = '';
    public $slug = '';
    public $perPage = 10;

    public function mount() {
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
            'slug' => $this->isEditing ? 'required|string|unique:categories,slug,' . $this->category->id : 'required|string|unique:categories,slug',
        ];
    }

    public function confirmDelete($categoryId)
    {
        $this->categoryToDelete = $categoryId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $category = Category::find($this->categoryToDelete);
        if ($category) {
            $category->delete();
            flash()->success('Category deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->categoryToDelete = null;
    }

    #[Title('Categories')]
    public function with(): array
    {
        return [
            'categories' => $this->categories,
        ];
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="All Categories"
        :items="$categories"
        searchPlaceholder="Search Categories..."
        message="No categories available."
        :perPage="$perPage"
        createButtonLabel="Add Category"
        createButtonAbility="categories.create"
        createButtonRoute="categories.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M3 6a2 2 0 0 1 2-2h5.532a2 2 0 0 1 1.536.72l1.9 2.28H3V6Zm0 3v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9H3Z" clip-rule="evenodd"/>
            </svg>

        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Slug', 'Actions']"
            :rows="$categories->map(fn($category) => [
                $category->name,
                $category->slug,
                'actions-placeholder',
                '__model' => $category
            ])"
            editAbility="categories.edit"
            editParameter="category"
            editRoute="categories.edit"
            deleteAbility="categories.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Category"
            message="Are you sure you want to delete this category? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>