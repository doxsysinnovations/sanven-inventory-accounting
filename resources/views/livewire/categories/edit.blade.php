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

    public function mount(Category $category) 
    {
        $this->isEditing = true;
        $this->resetValidation();
        $this->category = $category;
        $this->name = $category->name;
        $this->slug = $category->slug;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => $this->isEditing ? 'required|string|unique:categories,slug,' . $this->category->id : 'required|string|unique:categories,slug',
        ];
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
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
};

?>

<div>
   <x-categories-form 
        :is-editing="true"
    />
</div>