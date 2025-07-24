<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Agent;
use App\Models\Product;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $quotation;
    public $confirmingDelete = false;
    public $quotationToDelete;
    public $perPage = 5;

    public function mount()
    {
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();
        $this->perPage = session('perPage', 5);
    }

    public function updatedPerPage($value)
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

    public function confirmDelete($quotationId)
    {
        $this->quotationToDelete = $quotationId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $quotation = Quotation::find($this->quotationToDelete);
        if ($quotation) {
            $quotation->delete();
            flash()->success('Quotation deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->quotationToDelete = null;
    }

    public function with(): array
    {
        return [
            'quotations' => $this->quotations,
        ];
    }

    public function getQuotationsProperty()
    {
        return Quotation::query()
            ->with(['customer', 'agent'])
            ->where(function ($query) {
                $query->where('quotation_number', 'like', '%' . $this->search . '%')->orWhereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }
};
?>

<div>
    <x-view-layout
        title="Quotation List"
        :items="$quotations"
        searchPlaceholder="Search Quotations..."
        message="No quotations available."
        :perPage="$perPage"
        createButtonLabel="Create Quotation"
        createButtonAbility="quotations.create"
        createButtonRoute="quotations.create"
    >
         <x-list-table
            :headers="['Quotation #', 'Customer', 'Amount', 'Status', 'Valid Until', 'Actions']"
            :rows="$quotations->map(fn($quotation) => [
                $quotation->quotation_number,
                $quotation->customer->name ?? 'N/A',
                number_format($quotation->total_amount, 2),
                $quotation->status,
                \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y'),
                'actions-placeholder',
                '__model' => $quotation
            ])"
            viewAbility="quotations.view"
            viewRoute="quotations.view"
            editParameter="quotation"
            editAbility="quotations.edit"
            editParameter="quotation"
            editRoute="quotations.edit"
            deleteAbility="quotations.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Quotation"
            message="Are you sure you want to delete this quotation? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>