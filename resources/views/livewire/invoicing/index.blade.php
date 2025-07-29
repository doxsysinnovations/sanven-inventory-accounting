<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use Livewire\Attributes\Title;

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

    public $showInvoiceModal = false;
    public $selectedInvoice = null;

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

    public function deleteInvoice()
    {
        try {
            if (!Gate::allows('invoicing.delete', $this->invoiceToDelete)) {
                throw new \Exception('You are not authorized to delete this invoice.');
            }

            $this->invoiceToDelete->delete();

            $this->dispatch('invoice-deleted', message: 'Invoice deleted successfully.');
            $this->resetDeleteModal();
        } catch (\Exception $e) {
            $this->deleteError = $e->getMessage();
        }
    }

    public function resetDeleteModal()
    {
        $this->reset(['invoiceToDelete', 'showDeleteModal', 'deleteError']);
    }

    #[Title('Invoices')]
    public function with(): array
    {
        return [
            'invoices' => $this->invoices,
        ];
    }

    public function showInvoice($invoiceId)
    {
        $this->selectedInvoice = Invoice::with(['customer', 'items.stock.product'])->find($invoiceId);
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal()
    {
        $this->reset(['selectedInvoice', 'showInvoiceModal']);
    }

    public function getInvoicesProperty()
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
            })
            ->latest()
            ->paginate($this->perPage);
    }
}; ?>

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
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Invoices</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Invoice List</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Manage all your invoices in one place</p>
        </div>
        <div>
            <a href="{{ route('invoicing.create') }}"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Invoice
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Search -->
        <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search invoices..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div>
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

        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select wire:model.live="status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Date Range -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
            <div class="flex items-center gap-2">
                <input wire:model.live="startDate" type="date"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <span class="text-gray-500 dark:text-gray-400">to</span>
                <input wire:model.live="endDate" type="date"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                @if ($startDate || $endDate)
                    <button wire:click="clearDateFilter" type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
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

    <!-- Invoice Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if ($invoices->isEmpty())
            <div class="p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No invoices found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new invoice.</p>
                <div class="mt-6">
                    <a href="{{ route('invoicing.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Invoice
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
                                Invoice #
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payment
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $invoice->invoice_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->customer->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $invoice->customer->email }}</div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100">
                                    Php {{ number_format($invoice->grand_total, 2) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($invoice->issued_date)->format('M d, Y') }} </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' =>
                                            $invoice->status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' =>
                                            $invoice->status === 'paid',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' =>
                                            $invoice->status === 'overdue',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100' =>
                                            $invoice->status === 'cancelled',
                                    ])>
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        @can('invoicing.show')
                                            <button wire:click="showInvoice({{ $invoice->id }})"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        @endcan
                                        @can('invoicing.edit')
                                            <a href="{{ route('invoicing.edit', $invoice->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('invoicing.delete')
                                            <button wire:click="confirmDelete({{ $invoice->id }})"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endcan

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                {{ $invoices->links() }}
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
                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100' =>
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
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">Sanven Medical Enterprises, Inc.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Blk. 22 Lot 10 Phase 2, Nevada St., Suburbia North, Malpitic 2000, City of San Fernando (Capital), Pampanga, Philippines</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tel. # (045) 455-1402; (045) 455-1517</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cel. Nos. 0932-888-3548/0932-888-3547</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">sanvenmedinc@yahoo.com.ph</p>
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

                    <!-- Invoice Items -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">INVOICE ITEMS</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
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
                                        {{-- <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Discount
                                        </th> --}}
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            VAT (12%)
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
                                                @if (!empty($item->notes))
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $item->notes }}</p>
                                                @endif
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ $item->quantity }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                Php {{ number_format($item->price, 2) }}
                                            </td>
                                            {{-- <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                Php {{ number_format($item->discount ?? 0, 2) }}
                                            </td> --}}
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                Php {{ number_format($item->tax ?? 0, 2) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-medium">
                                                Php {{ number_format($item->total, 2) }}
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
                                    <span>Php {{ number_format($selectedInvoice->total_amount ?? 0, 2) }}</span>
                                </div>
                                @if ($selectedInvoice->discount ?? 0 > 0)
                                    <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Discount</span>
                                        <span>- Php {{ number_format($selectedInvoice->discount ?? 0, 2) }}</span>
                                    </div>
                                @endif
                                @if ($selectedInvoice->tax ?? 0 > 0)
                                    <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Vatable Amount</span>
                                        <span>Php {{ number_format($selectedInvoice->tax ?? 0, 2) }}</span>
                                    </div>
                                @endif
                                <div
                                    class="flex justify-between py-2 text-lg font-medium text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                                    <span>Total</span>
                                    <span>Php {{ number_format($selectedInvoice->grand_total ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Payment Method</span>
                                    <span>{{ ucfirst(str_replace('_', ' ', $selectedInvoice->payment_method ?? 'N/A')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Notes -->
                    @if (!empty($selectedInvoice->notes))
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
</div>
