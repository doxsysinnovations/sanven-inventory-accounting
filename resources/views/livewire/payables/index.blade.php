<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use App\Models\Payable;
use Livewire\Attributes\Title;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $paymentMethod = '';
    public $status = '';
    public $startDate = '';
    public $endDate = '';
    public $perPage = 10;
    public $invoiceToDelete = null;
    public $showDeleteModal = false;
    public $deleteError = null;
    public $type = '';
    public $showInvoiceModal = false;
    public $selectedInvoice = null;
    public $showCreatePayableModal = false;
    public $showCreatePayableConfirm = false;
    public $payableForm = [
        'payable_no' => '',
        'payee_name' => '',
        'type' => '',
        'amount' => '',
        'due_date' => '',
        'status' => 'pending',
        'payment_method' => '',
        'remarks' => '',
    ];
    public $selectedPayable = null;
    public $showPayableModal = false;
    public $confirmingId = null;
    public $showConfirmModal = false;

    public function mount()
    {
        $this->perPage = session('perPage', 10);
    }

    public function updatedPerPage($value)
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPaymentMethod()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function clearDateFilter()
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function confirmDelete($invoiceId)
    {
        $this->invoiceToDelete = Invoice::find($invoiceId);
        $this->deleteError = null;
        $this->showDeleteModal = true;
    }

    public function resetDeleteModal()
    {
        $this->reset(['invoiceToDelete', 'showDeleteModal', 'deleteError']);
    }

    #[Title('Invoices')]
    public function with(): array
    {
        return [
            'payables' => $this->payables,
            'suppliers' => \App\Models\Supplier::orderBy('name')->get(),
        ];
    }

    public function openCreatePayableModal()
    {
        $this->reset(['payableForm']); // Reset the whole form first

        $latest = \App\Models\Payable::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $this->payableForm['payable_no'] = 'PAY-' . date('Ymd') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $this->showCreatePayableModal = true;
    }

    public function closeCreatePayableModal()
    {
        $this->showCreatePayableModal = false;
    }

    public function confirmCreatePayable()
    {
        $this->validate([
            'payableForm.payable_no' => 'required|string|max:50',
            'payableForm.payee_name' => 'required|string|max:100',
            'payableForm.type' => 'required|string|max:50',
            'payableForm.amount' => 'required|numeric|min:0',
            'payableForm.due_date' => 'required|date',
            'payableForm.status' => 'required|string',
            'payableForm.payment_method' => 'required|string',
            'payableForm.remarks' => 'nullable|string|max:255',
        ]);
        $this->showCreatePayableConfirm = true;
    }

    public function savePayable()
    {
        $this->validate([
            'payableForm.payable_no' => 'required|string|max:50',
            'payableForm.payee_name' => 'required|string|max:100',
            'payableForm.type' => 'required|string|max:50',
            'payableForm.amount' => 'required|numeric|min:0',
            'payableForm.due_date' => 'required|date',
            'payableForm.status' => 'required|string',
            'payableForm.payment_method' => 'required|string',
            'payableForm.remarks' => 'nullable|string|max:255',
        ]);

        Payable::create($this->payableForm);
        $this->showCreatePayableModal = false;
        $this->reset(['payableForm']);
        $this->dispatch('payable-created');
    }

    public function closeInvoiceModal()
    {
        $this->reset(['selectedInvoice', 'showInvoiceModal']);
    }

    public function getTotalPayablesCountProperty()
    {
        return $this->filteredPayablesQuery()->count();
    }

    public function getTotalPayablesAmountProperty()
    {
        return $this->filteredPayablesQuery()->sum('amount');
    }

    public function getPaidPayablesCountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'paid')->count();
    }

    public function getPaidPayablesAmountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'paid')->sum('amount');
    }

    public function getApprovedPayablesCountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'approved')->count();
    }

    public function getApprovedPayablesAmountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'approved')->sum('amount');
    }

    public function getDeliveredPayablesCountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'delivered')->count();
    }

    public function getDeliveredPayablesAmountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'delivered')->sum('amount');
    }

    public function getPendingPayablesCountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'pending')->count();
    }

    public function getPendingPayablesAmountProperty()
    {
        return $this->filteredPayablesQuery()->where('status', 'pending')->sum('amount');
    }

    public function getPayablesProperty()
    {
        return $this->filteredPayablesQuery()->latest()->paginate($this->perPage);
    }

    protected function filteredInvoicesQuery()
    {
        return Invoice::with('customer')
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')->orWhereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->paymentMethod, fn($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('issued_date', [$this->startDate, $this->endDate]);
            })
            ->when($this->startDate && !$this->endDate, function ($query) {
                $query->where('issued_date', '>=', $this->startDate);
            })
            ->when($this->endDate && !$this->startDate, function ($query) {
                $query->where('issued_date', '<=', $this->endDate);
            });
    }

    protected function filteredPayablesQuery()
    {
        return Payable::query()
            ->when($this->search, function ($query) {
                $query->where('payable_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->paymentMethod, fn($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('issued_date', [$this->startDate, $this->endDate]);
            })
            ->when($this->startDate && !$this->endDate, function ($query) {
                $query->where('issued_date', '>=', $this->startDate);
            })
            ->when($this->endDate && !$this->startDate, function ($query) {
                $query->where('issued_date', '<=', $this->endDate);
            })
            ->when($this->type, fn($q) => $q->where('type', $this->type));
    }
    public function loadPayable($id)
    {
        $this->selectedPayable = Payable::find($id);
    }
    public function confirmApprove($id)
    {
        $this->confirmingId = $id;
        $this->showConfirmModal = true;
    }

    public function cancelConfirm()
    {
        $this->confirmingId = null;
        $this->showConfirmModal = false;
    }

    public function approve($id)
    {
        $payable = Payable::findOrFail($id);

        // Optional: Check if already approved
        if ($payable->status === 'approved') {
            return;
        }

        $payable->status = 'approved';
        $payable->save();

        session()->flash('message', 'Payable approved successfully.');
    }
    public function markAsPaid(int $id)
    {
        $payable = Payable::findOrFail($id);

        $payable->update([
            'status' => 'paid',
            'payment_date' => Carbon::now(), // optional
        ]);

        $this->dispatch('notify', 'Marked as paid.');
    }
};

?>

<div>
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                        <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Payables</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">List of Payables</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Monitor and organize your outstanding payables with
                ease.</p>
        </div>
        <div>
            {{-- <a href="{{ route('invoicing.create') }}"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Payable
            </a> --}}

        </div>
    </div>

    <!-- Filters Toggle Button -->
    <div class="flex items-center justify-between mb-4">
        <button x-data x-on:click="$refs.filters.classList.toggle('hidden')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 cursor-pointer text-white text-sm font-medium rounded-lg shadow hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400"
            type="button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0013 14.414V19a1 1 0 01-1 1h-2a1 1 0 01-1-1v-4.586a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <span>Show/Hide Filters</span>
        </button>
        <button wire:click="openCreatePayableModal"
            class="flex items-center gap-2 px-4 py-2 cursor-pointer bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            Create Payable
        </button>
    </div>

    <!-- Filters Section -->
    <div x-ref="filters"
        class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6 bg-gray-50 dark:bg-gray-900 rounded-lg p-4 shadow transition-all hidden">

        <!-- Search -->
        <div class="md:col-span-2 flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by invoice #, supplier, or reference..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 pr-10 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Supplier -->
        <div class="flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier</label>
            <select wire:model.live="supplier"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="">All Suppliers</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Status -->
        <div class="flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select wire:model.live="status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Payment Method -->
        <div class="flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
            <select wire:model.live="paymentMethod"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="credit_card">Credit Card</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="paypal">PayPal</option>
                <option value="other">Other</option>
            </select>
        </div>

        <!-- Date Type + Range -->
        <div class="md:col-span-2 flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Filter</label>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <select wire:model.live="dateType"
                    class="w-full sm:w-4/12 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                    <option value="issued_date">Issued Date</option>
                    <option value="due_date">Due Date</option>
                    <option value="payment_date">Payment Date</option>
                </select>
                <input wire:model.live="startDate" type="date"
                    class="w-full sm:w-4/12 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <input wire:model.live="endDate" type="date"
                    class="w-full sm:w-4/12 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
            </div>
        </div>

        <!-- Amount Range -->
        <div class="md:col-span-2 flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount Range</label>
            <div class="flex items-center gap-2">
                <input wire:model.live="minAmount" type="number" placeholder="Min"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <span class="text-gray-500 dark:text-gray-400">–</span>
                <input wire:model.live="maxAmount" type="number" placeholder="Max"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
            </div>
        </div>

        <!-- Type -->
        <div class="flex flex-col justify-end">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select wire:model.live="type"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="">All Types</option>
                <option value="agent_commission">Agent commission</option>
                <option value="Utilities">Utilities</option>
                <option value="Rent">Rent</option>
                <option value="Supplies">Supplies</option>
                <!-- Add more types as needed -->
            </select>
        </div>
    </div>


    <div class="flex justify-between overflow-x-auto mb-8 gap-6">

        <!-- Total -->
        <div
            class="flex gap-3 items-center sm:w-3/12 w-full cursor-pointer p-5 rounded-lg 
               bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-gray-500 bg-gray-50 dark:bg-blue-900/20 
                   text-gray-500 flex justify-center items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-2.5 0-4 1.5-4 4s1.5 4 4 4 4-1.5 4-4-1.5-4-4-4z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 12c0-5 4-8 8-8s8 3 8 8-4 8-8 8-8-3-8-8z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Total</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->totalPayablesCount }} payables</p>
                <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($this->totalPayablesAmount, 2) }}
                </h6>
            </div>
        </div>

        <div
            class="flex gap-3 items-center sm:w-3/12 w-full cursor-pointer p-5 rounded-lg 
       bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="shrink-0 relative h-14 w-14 rounded-full border-2 border-blue-500 bg-blue-50 dark:bg-blue-900/20 
           text-blue-500 flex justify-center items-center">
                <!-- Approved Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2l4 -4m5 2a9 9 0 11-18 0a9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Approved</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $this->approvedPayablesCount }} payables
                </p>
                <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($this->approvedPayablesAmount, 2) }}
                </h6>
            </div>
        </div>

        <!-- Paid Card -->
        <div
            class="flex gap-3 items-center sm:w-3/12 w-full cursor-pointer p-5 rounded-lg 
       bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-green-500 bg-green-50 dark:bg-green-900/20 
           text-green-500 flex justify-center items-center">
                <!-- Paid Icon: Cash or Dollar Sign -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-1.333 0-4 0.4-4 2s2.667 2 4 2s4 0.4 4 2s-2.667 2-4 2m0-10v2m0 8v2m0-12a9 9 0 100 18 9 9 0 000-18z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Paid</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $this->paidPayablesCount }} payables
                </p>
                <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($this->paidPayablesAmount, 2) }}
                </h6>
            </div>
        </div>

        <!-- Pending -->
        <div
            class="flex gap-3 items-center sm:w-3/12 w-full cursor-pointer p-5 rounded-lg 
               bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 
                   text-yellow-500 flex justify-center items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Pending</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->pendingPayablesCount }} payables</p>
                <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($this->pendingPayablesAmount, 2) }}
                </h6>
            </div>
        </div>

        <!-- Cancelled -->
        <div
            class="flex gap-3 items-center sm:w-3/12 w-full cursor-pointer p-5 rounded-lg 
               bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-purple-500 bg-purple-50 dark:bg-purple-900/20 
                   text-purple-500 flex justify-center items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Cancelled</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->deliveredPayablesCount }} payables</p>
                <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($this->deliveredPayablesAmount, 2) }}
                </h6>
            </div>
        </div>

    </div>


    <!-- Per Page Selector -->
    <div class="flex justify-end mb-4">
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <span>Show</span>
            <select wire:model.live="perPage"
                class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-2 py-1 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>entries</span>
        </label>
    </div>

    <!-- Payables Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if ($payables->isEmpty())
            <div class="p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No payables found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new invoice.</p>
                <div class="mt-6">
                    <a href="{{ route('invoicing.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Payable
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payable #
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payee
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payment Method
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($payables as $payable)
                            <tr wire:key="payable-{{ $payable->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">

                                {{-- Payable No --}}
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $payable->payable_no }}
                                </td>

                                {{-- Payee (Name + Email if available) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $payable->payee->name ?? ($payable->payee_name ?? 'N/A') }}
                                    </div>
                                    @if (!empty($payable->payee->email))
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $payable->payee->email }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Type --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $payable->type ?? 'N/A' }}
                                </td>

                                {{-- Amount --}}
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100">
                                    ₱{{ number_format($payable->amount, 2) }}
                                </td>

                                {{-- Due Date --}}
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $payable->due_date ? \Carbon\Carbon::parse($payable->due_date)->format('M d, Y') : '—' }}
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' =>
                                            $payable->status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' =>
                                            $payable->status === 'paid',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' =>
                                            $payable->status === 'overdue',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100' =>
                                            $payable->status === 'cancelled',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100' =>
                                            $payable->status === 'approved',
                                    ])>
                                        {{ ucfirst($payable->status) }}
                                    </span>

                                </td>

                                {{-- Payment Method --}}
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $payable->payment_method ? ucfirst(str_replace('_', ' ', $payable->payment_method)) : '—' }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1">

                                    <flux:modal.trigger name="view-payable-{{ $payable->id }}">
                                        <button
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 
        hover:text-blue-800 dark:hover:text-blue-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 
        rounded cursor-pointer transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </button>
                                    </flux:modal.trigger>

                                    {{-- Modal --}}
                                    <flux:modal name="view-payable-{{ $payable->id }}" class="md:w-[600px]">
                                        <div class="p-6 text-left space-y-6">
                                            <!-- Payable Header -->
                                            <div>
                                                <h2 class="text-xl font-semibold text-foreground">Payable Details</h2>
                                                <p class="text-sm text-muted-foreground">Review the full information of
                                                    this payable entry.</p>
                                            </div>

                                            <!-- Payable Info Grid -->
                                            <div class="grid grid-cols-12 gap-5">
                                                <div class="col-span-6">
                                                    <p class="text-sm text-muted-foreground">Reference #</p>
                                                    <h5 class="text-base font-semibold">
                                                        {{ $payable->reference_no ?? 'N/A' }}</h5>
                                                </div>
                                                <div class="col-span-6">
                                                    <p class="text-sm text-muted-foreground">Amount</p>
                                                    <h5 class="text-base font-semibold">
                                                        ₱{{ number_format($payable->amount, 2) }}</h5>
                                                </div>

                                                <div class="col-span-6">
                                                    <p class="text-sm text-muted-foreground">Status</p>
                                                    <h5 class="text-base font-semibold">
                                                        {{ ucfirst($payable->status) }}</h5>
                                                </div>
                                                <div class="col-span-6">
                                                    <p class="text-sm text-muted-foreground">Due Date</p>
                                                    <h5 class="text-base font-semibold">
                                                        {{ \Carbon\Carbon::parse($payable->due_date)->toFormattedDateString() }}
                                                    </h5>
                                                </div>

                                                <div class="col-span-12">
                                                    <p class="text-sm text-muted-foreground">Payee</p>
                                                    <h5 class="text-base font-semibold">{{ $payable->payee_name }}
                                                    </h5>
                                                </div>

                                                <div class="col-span-12">
                                                    <p class="text-sm text-muted-foreground">Supplier</p>
                                                    <h5 class="text-base font-semibold">
                                                        {{ $payable->supplier->name ?? 'N/A' }}</h5>
                                                </div>

                                                @if ($payable->notes)
                                                    <div class="col-span-12">
                                                        <p class="text-sm text-muted-foreground mb-1">Notes</p>
                                                        <p class="text-base leading-relaxed text-foreground">
                                                            {{ $payable->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Divider -->
                                            <hr class="border-t border-gray-200 dark:border-gray-700">

                                            <!-- Footer Actions -->
                                            <div class="flex justify-end gap-3">
                                                <button type="button"
                                                    class="h-10 px-5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl">
                                                    Edit
                                                </button>
                                                <button type="button"
                                                    class="h-10 px-5 text-sm font-medium text-red-600 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-xl">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>

                                    </flux:modal>



                                    {{-- Approve --}}
                                    @if ($payable->status === 'pending')
                                        <button wire:click="approve({{ $payable->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 dark:text-green-400 
        hover:text-green-800 dark:hover:text-green-200 hover:bg-green-50 dark:hover:bg-green-900/30
        rounded cursor-pointer transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </button>
                                    @endif

                                    {{-- Pay Now --}}
                                    @if ($payable->status === 'approved')
                                        <button wire:click="markAsPaid({{ $payable->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 
                   hover:text-indigo-800 dark:hover:text-indigo-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                   rounded cursor-pointer transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 9V7a4 4 0 00-8 0v2m-2 4h12m-6 4v2m0-6v2" />
                                            </svg>
                                            Pay
                                        </button>
                                    @endif

                                    {{-- Receipt --}}
                                    {{-- @if ($payable->status === 'paid')
                                        <a href="{{ route('payables.receipt', $payable->id) }}"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 
                   hover:text-gray-800 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 
                   rounded cursor-pointer transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 17v2a2 2 0 104 0v-2m-4 0V5a2 2 0 012-2h4a2 2 0 012 2v12m-4 0H9" />
                                            </svg>
                                            Receipt
                                        </a>
                                    @endif --}}

                                </td>



                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                {{ $payables->links() }}
            </div>
        @endif
    </div>

    <!-- Invoice Detail Modal -->
    <div x-cloak x-data="{ show: @entangle('showInvoiceModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                id="modal-title">
                                Invoice #{{ $selectedInvoice->invoice_number ?? '' }}
                            </h3>
                            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Issued:
                                    {{ \Carbon\Carbon::parse($selectedInvoice->issued_date ?? '')->format('M d, Y') }}
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Due:
                                    {{ \Carbon\Carbon::parse($selectedInvoice->due_date ?? '')->format('M d, Y') }}
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path
                                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                    Customer: {{ $selectedInvoice->customer->name ?? '' }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <span @class([
                                'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' =>
                                    isset($selectedInvoice) && $selectedInvoice->status === 'pending',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' =>
                                    isset($selectedInvoice) && $selectedInvoice->status === 'paid',
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' =>
                                    isset($selectedInvoice) && $selectedInvoice->status === 'overdue',
                                'bg-gray-50 text-gray-800 dark:bg-gray-700 dark:text-gray-100' =>
                                    isset($selectedInvoice) && $selectedInvoice->status === 'cancelled',
                            ])>
                                {{ isset($selectedInvoice) ? ucfirst($selectedInvoice->status) : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">BILL FROM</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">Your Company Name</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">123 Business Street</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">City, State 12345</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Phone: (123) 456-7890</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Email: billing@yourcompany.com</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">BILL TO</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ $selectedInvoice->customer->name ?? '' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $selectedInvoice->customer->address ?? '' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $selectedInvoice->customer->phone ?? '' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $selectedInvoice->customer->email ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Payables Items -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">INVOICE ITEMS</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Item
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Qty
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Discount
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Tax
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($selectedInvoice->items ?? [] as $item)
                                        <tr>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item->product_name ?? 'N/A' }}
                                                @if (!empty($item->remarks))
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $item->remarks }}</p>
                                                @endif
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ $item->quantity }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                ₱{{ number_format($item->price, 2) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                ₱{{ number_format($item->discount ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                ₱{{ number_format($item->tax ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-medium">
                                                ₱{{ number_format($item->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="mt-6 flex justify-end">
                        <div class="w-full max-w-md">
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Subtotal</span>
                                    <span>₱{{ number_format($selectedInvoice->total_amount ?? 0, 2) }}</span>
                                </div>
                                @if ($selectedInvoice->discount ?? 0 > 0)
                                    <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Discount</span>
                                        <span>- ₱{{ number_format($selectedInvoice->discount ?? 0, 2) }}</span>
                                    </div>
                                @endif
                                @if ($selectedInvoice->tax ?? 0 > 0)
                                    <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Tax</span>
                                        <span>₱{{ number_format($selectedInvoice->tax ?? 0, 2) }}</span>
                                    </div>
                                @endif
                                <div
                                    class="flex justify-between py-2 text-lg font-medium text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                                    <span>Total</span>
                                    <span>₱{{ number_format($selectedInvoice->grand_total ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Payment Method</span>
                                    <span>{{ ucfirst(str_replace('_', ' ', $selectedInvoice->payment_method ?? 'N/A')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Notes -->
                    @if (!empty($selectedInvoice->remarks))
                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">NOTES</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedInvoice->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeInvoiceModal"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                    {{-- <a href="{{ route('invoicing.download', $selectedInvoice->id ?? '') }}" target="_blank" --}}
                    <a href="#" target="_blank"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-cloak x-data="{ show: @entangle('showDeleteModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                id="modal-title">
                                Delete Invoice
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to delete invoice #<span
                                        class="font-semibold">{{ $invoiceToDelete->invoice_number ?? '' }}</span>?
                                    This action cannot be undone.
                                </p>
                                @if ($deleteError)
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ $deleteError }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="deleteInvoice" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button wire:click="resetDeleteModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast Notification -->
    <div x-data="{ show: false, message: '' }" x-show="show"
        x-on:invoice-deleted.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-4 right-4 z-50">
        <div class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span x-text="message"></span>
        </div>
    </div>

    <!-- Modern Create Payable Modal - Two Column Layout -->
    <div x-cloak x-data="{ show: @entangle('showCreatePayableModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form wire:submit.prevent="savePayable">
                    <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4 sm:p-8 sm:pb-4">
                        <h3
                            class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Payable
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Record a new payable and manage its payment details.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Payable #</label>
                                    <input disabled wire:model.defer="payableForm.payable_no" type="text"
                                        placeholder="e.g. PAY-20250728-001"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"
                                        autocomplete="off">
                                    <span class="text-xs text-gray-400">Unique reference for this payable.</span>
                                    @error('payableForm.payable_no')
                                        <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payee
                                        Name</label>
                                    <input wire:model.defer="payableForm.payee_name" type="text"
                                        placeholder="Who will be paid?"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                    <span class="text-xs text-gray-400">Person or company receiving payment.</span>
                                    @error('payableForm.payee_name')
                                        <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Type of Payable
                                    </label>
                                    <select wire:model.defer="payableForm.type"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                        <option value="">-- Select Type --</option>
                                        <option value="utilities">Utilities</option>
                                        <option value="rent">Rent</option>
                                        <option value="supplies">Supplies</option>
                                        <option value="payroll">Payroll</option>
                                        <option value="agent_commission">Commission</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="internet_communication">Internet & Communication</option>
                                        <option value="marketing">Marketing</option>
                                        <option value="subscription">Subscription</option>
                                        <option value="others">Others</option>
                                    </select>
                                    <span class="text-xs text-gray-400">Category of the payable item or service.</span>
                                    @error('payableForm.type')
                                        <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                            <!-- Right Column -->
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-gray-400">₱</span>
                                        <input wire:model.defer="payableForm.amount" type="number" step="0.01"
                                            placeholder="0.00"
                                            class="pl-7 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                    </div>
                                    <span class="text-xs text-gray-400">Amount to be paid.</span>

                                    @error('payableForm.amount')
                                        <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due
                                            Date</label>
                                        <input wire:model.defer="payableForm.due_date" type="date"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                        <span class="text-xs text-gray-400">Date when payment is due.</span>
                                        @error('payableForm.due_date')
                                            <span class="text-xs text-red-600">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                        <select wire:model.defer="payableForm.status"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="paid">Paid</option>
                                            <option value="overdue">Overdue</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        @error('payableForm.status')
                                            <span class="text-xs text-red-600">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment
                                        Method</label>
                                    <select wire:model.defer="payableForm.payment_method"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('payableForm.payment_method')
                                        <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks</label>
                            <textarea wire:model.defer="payableForm.remarks" placeholder="Additional details or instructions..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200"></textarea>
                            @error('payableForm.remarks')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="confirmCreatePayable"
                            class="w-full inline-flex items-center cursor-pointer justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Save Payable
                        </button>
                        <button type="button" wire:click="closeCreatePayableModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-cloak x-data="{ show: @entangle('showCreatePayableConfirm') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <!-- Modal panel -->
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4">
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Confirm Create Payable</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
                        Are you sure you want to create this payable? Please confirm to proceed.
                    </p>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="savePayable"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            Yes, Create
                        </button>
                        <button type="button" wire:click="$set('showCreatePayableConfirm', false)"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400">
                            No, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
