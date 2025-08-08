<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $customer;
    public $confirmingDelete = false;
    public $customerToDelete;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $customerInfo;
    public $perPage = 5;

    public function mount()
    {
        $this->perPage = session('perPage', 5);
    }

    public function updatedPerPage($value)
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

    public function confirmDelete($customerId)
    {
        $this->customerToDelete = $customerId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $customer = Customer::find($this->customerToDelete);
        if ($customer) {
            $customer->delete();
            flash()->success('Customer deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->customerToDelete = null;
    }

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
            ->paginate($this->perPage);
    }
};

?>

<div>
    <div>
        <x-view-layout
            title="Customers"
            :items="$customers"
            :perPage="$perPage"
            searchPlaceholder="Search Customers..."
            message="No existing customers."
            createButtonLabel="Add Customer"
            createButtonAbility="customers.create"
            createButtonRoute="customers.create"
        >
            <x-slot:emptyIcon>
                <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z" clip-rule="evenodd"/>
                </svg>
            </x-slot:emptyIcon>
                <x-list-table
                    :headers="['Name', 'Email', 'Phone Number', 'Address', 'Actions']"
                    :rows="$customers->map(fn($customer) => [
                        $customer->name,
                        $customer->email,
                        !empty($customer->phone) ? $customer->phone : 'No phone number available.',
                        !empty($customer->address) ? $customer->address : 'No address available.',
                        '__model' => $customer
                    ])"
                    viewAbility="customers.info"
                    viewRoute="customers.view"
                    editAbility="customers.edit"
                    editParameter="customer"
                    editRoute="customers.edit"
                    deleteAbility="customers.delete"
                    deleteAction="confirmDelete"
                />
        </x-view-layout>

        @if ($confirmingDelete)
            <x-delete-modal 
                title="Delete Customer"
                message="Are you sure you want to delete this customer? This action cannot be undone."
                onCancel="$set('confirmingDelete', false)"
            />
        @endif
    </div>
</div>