<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ProductType;
use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

new class extends Component {
    use WithPagination;
    #[Url]
    public $search = '';
    #[Url]
    public $selectedCategory = '';
    public $showModal = false;
    public $type;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $typeToDelete;
    public $name = '';
    public $description = '';
    public $category_id = '';
    public $categories = [];
    public $perPage = 10;

    public function mount()
    {
        $this->categories = Category::all();
        $this->perPage = session('perPage', 10);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function confirmDelete($typeId)
    {
        $this->typeToDelete = $typeId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $type = ProductType::find($this->typeToDelete);
        if ($type) {
            $type->delete();
            flash()->success('Type deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->typeToDelete = null;
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->category_id = '';
        $this->type = null;
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    #[Title('Types')]
    public function with(): array
    {
        return [
            'types' => $this->types,
        ];
    }

    public function getTypesProperty()
    {
        return ProductType::query()
            ->with('category')
            ->where('name', 'like', '%' . $this->search . '%')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="All Types"
        :items="$types"
        searchPlaceholder="Search Types..."
        :withFilter=true
        :filterItems="$categories"
        message="No types available."
        :perPage="$perPage"
        createButtonLabel="Add Types"
        createButtonAbility="types.create"
        createButtonRoute="types.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path fill="currentColor" d="M9.98189 4.50602c1.24881-.67469 2.78741-.67469 4.03621 0l3.9638 2.14148c.3634.19632.6862.44109.9612.72273l-6.9288 3.60207L5.20654 7.225c.2403-.22108.51215-.41573.81157-.5775l3.96378-2.14148ZM4.16678 8.84364C4.05757 9.18783 4 9.5493 4 9.91844v4.28296c0 1.3494.7693 2.5963 2.01811 3.2709l3.96378 2.1415c.32051.1732.66011.3019 1.00901.3862v-7.4L4.16678 8.84364ZM13.009 20c.3489-.0843.6886-.213 1.0091-.3862l3.9638-2.1415C19.2307 16.7977 20 15.5508 20 14.2014V9.91844c0-.30001-.038-.59496-.1109-.87967L13.009 12.6155V20Z"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Description', 'Category', 'Actions']"
            :rows="$types->map(fn($type) => [
                $type->name,
                $type->description,
                $type->category->name ?? 'Not categorized.',
                'actions-placeholder',
                '__model' => $type
            ])"
            editAbility="types.edit"
            editParameter="type"
            editRoute="types.edit"
            deleteAbility="types.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Type"
            message="Are you sure you want to delete this type? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>