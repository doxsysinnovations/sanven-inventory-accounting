<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Category;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $category;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $categoryToDelete;
    public $name = '';
    public $slug = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => $this->isEditing ? 'required|string|unique:categories,slug,' . $this->category->id : 'required|string|unique:categories,slug',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the category name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',
        ];
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->category->update([
                'name' => strtoupper($this->name),
                'slug' => strtolower($this->slug)
            ]);
            flash()->success('Category updated successfully!');
        } else {
            Category::create([
                'name' => strtoupper($this->name),
                'slug' => strtolower($this->slug)
            ]);
            flash()->success('Category created successfully!');
        }

        return redirect()->route('categories');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->category = null;
        $this->resetValidation();
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
            ->paginate(10);
    }
    
    public function cancel() 
    {
        $this->resetForm();
    }
};

?>

<div>
    <x-categories-form 
        :is-editing="false"
    />
</div>