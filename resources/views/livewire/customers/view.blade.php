<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $customer;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $activeTab = 'basic';
    public $customerInfo;

    public  function mount(Customer $id) {
        $this->customerInfo = $id;
        $this->activeTab = 'basic';
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
            ->paginate(10);
    }
};

?>

<div>
    <x-profile-layout 
        title="Customer Profile"
        modelInstance="customer"
        :modelInfo="$customerInfo"
        editRoute="customers.edit"
        :activeTab="$activeTab"
        :tabs="[
            ['key' => 'basic', 'label' => 'Basic Info'],
            ['key' => 'history', 'label' => 'History'],
        ]"
        :headerDetails="[
            ['label' => 'Email', 'value' => $customerInfo->email],
            ['label' => 'Address', 'value' => $customerInfo->address ?? 'Not provided.'],
            ['label' => 'Phone Number', 'value' => $customerInfo->phone ?? 'Not provided.'],
        ]"
        :tabContents="[
            'basic' => [
                ['label' => 'Customer Name', 'value' => $customerInfo->name],
                ['label' => 'Created At', 'value' => $customerInfo->created_at->format('M d, Y h:i A')],
                ['label' => 'Customer ID', 'value' => $customerInfo->id],
                ['label' => 'Last Updated', 'value' => $customerInfo->updated_at->format('M d, Y h:i A')],
            ],
            'history' => [] // or populate with actual history data
        ]"
        :emptyState="[
            'title' => 'No activity yet.',
            'description' => 'Customer history will appear here when available.'
        ]"
    />
</div>