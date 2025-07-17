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

    public function mount(Customer $customer) 
    {
        $this->isEditing = true;
        $this->resetValidation();
        $this->customer = $customer;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
    }

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
            'name.string' => 'The name should only contain letters and spaces.',
            'name.max' => 'Name is too long. Please keep it under 255 characters.',

            'email.required' => 'Please enter an email address.',
            'email.email' => 'Hmm... that doesn’t look like a valid email.',
            'email.unique' => 'Email is already in use. Try another one.',

            'phone.string' => 'The phone number must be plain text.',
            'phone.max' => 'Phone number is too long.',

            'address.string' => 'The address should only contain letters, numbers, and symbols.',
            'address.max' => 'Address is too long. Keep it under 255 characters.',
        ];
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

        return redirect()->route('customers');
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
        if($this->isEditing) {
            return redirect()->route('customers');
        }
    }
};

?>

<div>
    <x-customers-form
        :is-editing="true"
    />
</div>
