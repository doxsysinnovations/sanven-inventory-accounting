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

    public function mount()
    {
        $this->customers = Customer::all();
        $this->agents = Agent::all();
        $this->products = Product::all();
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
            ->paginate(10);
    }
};
?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex flex-col bg-white rounded-lg">
            <div class="bg-gray-50 p-6 flex items-center rounded-t-lg">
                <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                    Quotation List
                </h3>
            </div>
            <div class="px-8 pb-4">
                <div class="flex my-5">
                    <div class="ml-auto w-1/3 relative">
                        <x-search-bar placeholder="Search Quotations..."/>
                    </div>
                </div>
                <div>
                    @if ($quotations->isEmpty())
                        <div class="flex flex-col items-center justify-center p-8">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <p class="mb-2 font-bold text-gray-500 dark:text-gray-400">No quotations found.</p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 shadow-xs">
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
                            editAbility="quotations.edit"
                            editRoute="quotations.edit"
                            deleteAbility="quotations.delete"
                            deleteAction="confirmDelete"
                        />


                        </div>

                        <div class="mt-4">
                             {{ $quotations->links() }}
                        </div>
                    @endif 
                </div>
            </div>
        </div> 
    </div>

    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Delete Quotation
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Are you sure you want to delete this quotation? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="delete"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-400 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="$set('confirmingDelete', false)"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
