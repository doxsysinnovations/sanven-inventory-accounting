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


public function getTotalInvoicesCountProperty()
{
    return $this->filteredInvoicesQuery()->count();
}

public function getTotalInvoicesAmountProperty()
{
    return $this->filteredInvoicesQuery()->sum('grand_total');
}

public function getToDeliverInvoicesCountProperty()
{
    return $this->filteredInvoicesQuery()->where('status', 'to_deliver')->count();
}

public function getToDeliverInvoicesAmountProperty()
{
    return $this->filteredInvoicesQuery()->where('status', 'to_deliver')->sum('grand_total');
}

public function getDeliveredInvoicesCountProperty()
{
    return $this->filteredInvoicesQuery()->where('status', 'delivered')->count();
}

public function getDeliveredInvoicesAmountProperty()
{
    return $this->filteredInvoicesQuery()->where('status', 'delivered')->sum('grand_total');
}

public function getPendingInvoicesCountProperty()
{
    return $this->filteredInvoicesQuery()->where('status', 'pending')->count();
}

public function getPendingInvoicesAmountProperty()
{
    return $this->filteredInvoicesQuery()->where('status', 'pending')->sum('grand_total');
}
    public function getInvoicesProperty()
    {
        return $this->filteredInvoicesQuery()
        ->latest()
        ->paginate($this->perPage);
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
}; ?>

<div>
    <x-view-layout
        title="Invoice List"
        description="Manage all your invoices in one place."
        :items="$invoices"
        :withSearch="true"
        searchPlaceholder="Search Invoices..."
        message="No invoices available."
        :withDateFilter="true"
        :withPaymentMethodFilter="true"
        :withStatusFilter="true"
        :perPage="$perPage"
        createButtonLabel="Create Invoice"
        createButtonAbility="invoicing.create"
        createButtonRoute="invoicing.create"
    >
        <x-slot:statisticsSlot>
            <div class="flex justify-between overflow-x-auto mb-8 gap-6">
                <x-invoice-statistics-card
                    title="Total"
                    :label="$this->toDeliverInvoicesCount . ' invoices'"
                    :value="number_format($this->totalInvoicesAmount, 2)"
                >
                    <x-slot:iconSlot>
                        <div class="h-14 w-14 rounded-full border-2 border-blue-500 text-blue-500 flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                                <path
                                    d="M13.358 21c2.227 0 3.341 0 4.27-.533c.93-.532 1.52-1.509 2.701-3.462l.681-1.126c.993-1.643 1.49-2.465 1.49-3.379s-.497-1.736-1.49-3.379l-.68-1.126c-1.181-1.953-1.771-2.93-2.701-3.462C16.699 4 15.585 4 13.358 4h-2.637C9.683 4 8.783 4 8 4.024m-4.296 1.22C2.5 6.49 2.5 8.495 2.5 12.5s0 6.01 1.204 7.255c.998 1.033 2.501 1.209 5.196 1.239M7.5 7.995V17">
                                </path>
                            </svg>
                        </div>
                    </x-slot:iconSlot>
                </x-invoice-statistics-card>

                <x-invoice-statistics-card
                    title="To Deliver"
                    :label="$this->toDeliverInvoicesCount . ' invoices'"
                    :value="number_format($this->toDeliverInvoicesAmount, 2)"
                >
                    <x-slot:iconSlot>
                        <div class="h-14 w-14 rounded-full border-2 border-green-500 text-green-500 flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path
                                    d="M3 10.417c0-3.198 0-4.797.378-5.335c.377-.537 1.88-1.052 4.887-2.081l.573-.196C10.405 2.268 11.188 2 12 2s1.595.268 3.162.805l.573.196c3.007 1.029 4.51 1.544 4.887 2.081C21 5.62 21 7.22 21 10.417v1.574c0 5.638-4.239 8.375-6.899 9.536C13.38 21.842 13.02 22 12 22s-1.38-.158-2.101-.473C7.239 20.365 3 17.63 3 11.991z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 11.55L12.6 9a1 1 0 0 0-1.2 0L8 11.55m6 2.5l-2-1.5l-2 1.5"></path>
                            </svg>
                        </div>
                    </x-slot:iconSlot>
                </x-invoice-statistics-card>

                <x-invoice-statistics-card
                    title="Delivered"
                    :label="$this->pendingInvoicesCount . ' invoices'"
                    :value="number_format($this->pendingInvoicesAmount, 2)"
                >
                    <x-slot:iconSlot>
                        <div
                            class="h-14 w-14 rounded-full border-2 border-purple-500 text-purple-500 flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path
                                    d="M5 8.515C5 4.917 8.134 2 12 2s7 2.917 7 6.515c0 3.57-2.234 7.735-5.72 9.225a3.28 3.28 0 0 1-2.56 0C7.234 16.25 5 12.084 5 8.515Z">
                                </path>
                                <path d="M14 9a2 2 0 1 1-4 0a2 2 0 0 1 4 0Z"></path>
                                <path stroke-linecap="round"
                                    d="M20.96 15.5c.666.602 1.04 1.282 1.04 2c0 2.485-4.477 4.5-10 4.5S2 19.985 2 17.5c0-.718.374-1.398 1.04-2">
                                </path>
                            </svg>
                        </div>
                    </x-slot:iconSlot>
                </x-invoice-statistics-card>

                <x-invoice-statistics-card
                    title="Pending"
                    :label="$this->deliveredInvoicesCount . ' invoices'"
                    :value="number_format($this->deliveredInvoicesAmount, 2)"
                >
                    <x-slot:iconSlot>
                        <div class="h-14 w-14 rounded-full border-2 border-yellow-500 text-yellow-500 flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                                <path stroke-linejoin="round"
                                    d="m14.52 10.68l-.28-.28a3.168 3.168 0 1 0 .907 2.6m-.627-2.32L13 11m1.52-.32V9"></path>
                                <path
                                    d="M2 13.364c0-3.065 0-4.597.749-5.697a4.4 4.4 0 0 1 1.226-1.204c.72-.473 1.622-.642 3.003-.702c.659 0 1.226-.49 1.355-1.125A2.064 2.064 0 0 1 10.366 3h3.268c.988 0 1.839.685 2.033 1.636c.129.635.696 1.125 1.355 1.125c1.38.06 2.282.23 3.003.702c.485.318.902.727 1.226 1.204c.749 1.1.749 2.632.749 5.697s0 4.596-.749 5.697a4.4 4.4 0 0 1-1.226 1.204C18.904 21 17.343 21 14.222 21H9.778c-3.121 0-4.682 0-5.803-.735A4.4 4.4 0 0 1 2.75 19.06A3.4 3.4 0 0 1 2.277 18">
                                </path>
                            </svg>
                        </div>
                    </x-slot:iconSlot>
                </x-invoice-statistics-card>
            </div>
        </x-slot:statisticsSlot>
        <x-slot:emptyIcon>
            <svg class="w-32 h-32 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Invoice #', 'Customer', 'Amount', 'Date', 'Status', 'Payment', 'Actions']"
            :rows="$invoices->map(fn($invoice) => [
                $invoice->invoice_number ,
                view('livewire.invoicing.views.name-and-email', ['invoice' => $invoice])->render(),
                'Php ' . number_format($invoice->grand_total, 2),
                \Carbon\Carbon::parse($invoice->issued_date)->format('M d, Y'),
                $invoice->status,
                ucfirst(str_replace('_', ' ', $invoice->payment_method)),
                '__model' => $invoice
            ])"
            viewAbility="invoicing.view"
            viewRoute="invoicing.view"
            editAbility="invoicing.view"
            editParameter="invoice"
            editRoute="invoicing.edit"
            deleteAbility="invoicing.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($showDeleteModal)
        <x-delete-modal 
            title="Delete Invoice"
            message="Are you sure you want to delete this Invoice #{{ $invoiceToDelete->invoice_number ?? ''}}? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>