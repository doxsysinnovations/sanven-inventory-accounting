<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $supplier;
    public $confirmingDelete = false;
    public $supplierToDelete;
    public $name = '';
    public $contact_number = '';
    public $address = '';
    public $email = '';
    public $trade_name = '';
    public $identification_number = '';
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

    public function confirmDelete($supplierId)
    {
        $this->supplierToDelete = $supplierId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $supplier = Supplier::find($this->supplierToDelete);
        if ($supplier) {
            $supplier->delete();
            flash()->success('Supplier deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->supplierToDelete = null;
    }

    #[Title('Suppliers')]
    public function with(): array
    {
        return [
            'suppliers' => $this->suppliers,
        ];
    }

    public function info($supplierId)
    {
        $this->supplierInfo = Supplier::find($supplierId);
        $this->activeTab = 'basic';
        $this->infoModal = true;
    }

    public function getSuppliersProperty()
    {
        return Supplier::query()
            ->where('identification_number', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orWhere('trade_name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="Suppliers"
        :items="$suppliers"
        :perPage="$perPage"
        searchPlaceholder="Search Suppliers..."
        message="No exisiting suppliers."
        createButtonLabel="Add Suppliers"
        createButtonAbility="suppliers.create"
        createButtonRoute="suppliers.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['ID', 'Name', 'Trade Name', 'Contact Number', 'Email', 'Actions']"
            :rows="$suppliers->map(fn($supplier) => [
                $supplier->identification_number,
                $supplier->name,
                !empty($supplier->trade_name) ? $supplier->trade_name : 'No trade number available.',
                !empty($supplier->contact_number) ?  $supplier->contact_number : 'No contact number available.',
                !empty($supplier->email) ?  $supplier->email : 'No email available.',
                '__model' => $supplier
            ])"
            viewAbility="suppliers.view"
            viewRoute="suppliers.view"
            editAbility="suppliers.edit"
            editParameter="supplier"
            editRoute="suppliers.edit"
            deleteAbility="suppliers.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Supplier"
            message="Are you sure you want to delete this supplier? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>
