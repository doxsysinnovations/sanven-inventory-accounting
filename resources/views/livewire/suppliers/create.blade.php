<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $supplier;
    public $isEditing = false;
    public $supplierToDelete;
    public $name = '';
    public $contact_number = '';
    public $address = '';
    public $email = '';
    public $trade_name = '';
    public $identification_number = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'trade_name' => 'required|string|max:255',
            'identification_number' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => $this->isEditing ? 'nullable|email|unique:suppliers,email,' . $this->supplier->id : 'nullable|email|unique:suppliers,email',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the supplier’s name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',

            'trade_name.required' => 'Please enter the supplier’s trade name.',
            'trade_name.string' => 'Trade name should only contain letters and spaces.',
            'trade_name.max' => 'Trade name is too long.',

            'identification_number.required' => 'Please enter the supplier’s identification number.',
            'identification_number.string' => 'Identification number must be valid text.',
            'identification_number.max' => 'Identification number is too long.',

            'contact_number.string' => 'Please enter a valid contact number.',
            'contact_number.max' => 'The contact number is too long.',

            'address.string' => 'Please enter a valid address.',
            'address.max' => 'The address is too long. Please shorten it.',

            'email.email' => 'That doesn’t look like a valid email. Please check it again.',
            'email.unique' => 'This email is already being used by another supplier.',
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
            $this->supplier->update([
                'name' => $this->name,
                'trade_name' => $this->trade_name,
                'identification_number' => $this->identification_number,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'email' => $this->email,
            ]);
            flash()->success('Supplier updated successfully!');
        } else {
            Supplier::create([
                'name' => $this->name,
                'trade_name' => $this->trade_name,
                'identification_number' => $this->identification_number,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'email' => $this->email,
            ]);
            flash()->success('Supplier created successfully!');
        }

        return redirect()->route('suppliers');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->trade_name = '';
        $this->identification_number = '';
        $this->contact_number = '';
        $this->address = '';
        $this->email = '';
        $this->supplier = null;
        $this->resetValidation();
    }

    #[Title('Suppliers')]
    public function with(): array
    {
        return [
            'suppliers' => $this->suppliers,
        ];
    }

    public function getSuppliersProperty()
    {
        return Supplier::query()
            ->where('identification_number', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orWhere('trade_name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);
    }

    public function cancel(){
        $this->resetForm();
    }
};

?>

<x-suppliers-form 
    :is-editing="false" 
/>