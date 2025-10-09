<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $supplier;
    public $name = '';
    public $contact_number = '';
    public $address = '';
    public $email = '';
    public $trade_name = '';
    public $identification_number = '';

    public $supplierInfo;
    public $activeTab = 'basic';

    public function mount(Supplier $id) {
        $this->supplierInfo = $id;
        $this->activeTab = 'basic';
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
};

?>

<div>
    
<div>
    <x-profile-layout 
        title="Supplier Profile"
        modelInstance="supplier"
        :modelInfo="$supplierInfo"
        editRoute="suppliers.edit"
        :activeTab="$activeTab"
        :tabs="[
            ['key' => 'basic', 'label' => 'Basic Info'],
            ['key' => 'contact details', 'label' => 'Contact Details'],
        ]"
        :headerDetails="[
            ['label' => 'ID', 'value' => $supplierInfo->identification_number ?? 'Not provided.'],
            ['label' => 'Trade Name', 'value' => $supplierInfo->trade_name?? 'Not provided.'],
        ]"
        :tabContents="[
            'basic' => [
                ['label' => 'Supplier Name', 'value' => $supplierInfo->name],
                ['label' => 'Trade Name', 'value' => $supplierInfo->trade_name ?? 'Not provided.'],
                ['label' => 'Identification Number', 'value' => $supplierInfo->identification_number],
                ['label' => 'Created At', 'value' => $supplierInfo->created_at?->format('M d, Y h:i A') ?? 'Not provided.' ],
            ],
            'contact details' => [
                ['label' => 'Email', 'value' => $supplierInfo->email ?? 'Not provided.'],
                ['label' => 'Address', 'value' => $supplierInfo->address ?? 'Not provided.'],
                ['label' => 'Contact Number', 'value' => $supplierInfo->phone ?? 'Not provided.'],
            ]
        ]"
    />
</div>
