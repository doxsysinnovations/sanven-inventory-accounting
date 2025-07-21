<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $brand;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $brandToDelete;
    public $name = '';
    public $slug = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => $this->isEditing ? 'required|string|unique:brands,slug,' . $this->brand->id : 'required|string|unique:brands,slug',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the brand name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->brand->update([
                'name' => strtoupper($this->name),
                'slug' => strtolower($this->slug)
            ]);
            flash()->success('Brand updated successfully!');
        } else {
            Brand::create([
                'name' => strtoupper($this->name),
                'slug' => strtolower($this->slug)
            ]);
            flash()->success('Brand created successfully!');
        }

        return redirect()->route('brands');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->brand = null;
        $this->resetValidation();
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
            ->paginate(10);
    }

    public function cancel() 
    {
        $this->resetForm();
    }
};

?>

<div>
   <x-brands-form 
        :is-editing="false"
    />
</div>