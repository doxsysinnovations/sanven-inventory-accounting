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
    public $type;
    public $isEditing = false;
    public $typeToDelete;
    public $name = '';
    public $description = '';
    public $category_id = '';
    public $categories = [];

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the type name.',
            'name.string' => 'The name should be a valid text.',
            'name.max' => 'The name must not exceed 255 characters.',

            'description.required' => 'Please enter the description.',
            'description.string' => 'The description should be valid text.',

            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
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
            $this->type->update([
                'name' => $this->name,
                'description' => $this->description,
                'category_id' => $this->category_id,
            ]);
            flash()->success('Type updated successfully!');
        } else {
            ProductType::create([
                'name' => $this->name,
                'description' => $this->description,
                'category_id' => $this->category_id,
            ]);
            flash()->success('Type created successfully!');
        }

        return redirect()->route('types');
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
            ->paginate(10);
    }

    public function cancel() 
    {
        $this->resetForm();
    }
};

?>

<div>
    <x-types-form
        :is-editing="false"
        :categories="$categories"
    />
</div>