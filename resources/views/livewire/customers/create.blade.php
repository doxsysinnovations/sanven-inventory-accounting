<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $customer;
    public $isEditing = false;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $activeTab = 'basic';
    public $customerInfo;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => $this->isEditing ? 'required|email|unique:customers,email,' . $this->customer->id : 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the customer\'s name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',

            'email.required' => 'Please enter the customer\'s email address.',
            'email.email' => 'That doesn’t look like a valid email. Please check it again.',
            'email.unique' => 'This email is already being used by another customer.',

            'phone.string' => 'Please enter a valid phone number.',
            'phone.max' => 'The phone number is too long.',

            'address.string' => 'Please enter a valid address.',
            'address.max' => 'The address is too long. Please shorten it.',
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
            $this->customer->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address
            ]);
            flash()->success('Customer updated successfully!');
        } else {
            Customer::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address
            ]);
            flash()->success('Customer created successfully!');
        }

        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->customer = null;
        $this->resetValidation();
    }

    #[Title('Customers')]
    public function with(): array
    {
        return [
            'customers' => $this->customers,
        ];
    }

    public function getCustomersProperty()
    {
        return Customer::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);
    }
    
    public function cancel() 
    {
        $this->resetForm();
    }
};

?>

<div>
    <x-customers-form
        :is-editing="false"
    />
</div>
