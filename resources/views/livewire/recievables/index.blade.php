<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use Livewire\Attributes\Title;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use Livewire\WithFileUploads;

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
    public $invoice = null;
    public $collectAmount;
    public $collectMethod = 'cash';
    public $collectReference;
    public $collectNotes;
    public $payFull;
    public $collectDate; // New property for payment date
    public $paymentProof = []; // For multiple files
    // Add the trait at the top of your class
    use WithFileUploads;

    public function mount()
    {
        $this->perPage = session('perPage', 10);
        $this->collectDate = now()->toDateString(); // Set default payment date to today
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

    public function collectPayment($invoiceId)
    {
        $this->validate([
            'collectAmount' => 'required|numeric|min:1',
            'collectMethod' => 'required|string',
            'collectReference' => 'nullable|string|max:255',
            'collectNotes' => 'nullable|string',
            'collectDate' => ['required', 'date', 'before_or_equal:today'],
            'paymentProof.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $invoice = \App\Models\Invoice::findOrFail($invoiceId);

        // Prevent overpayment
        $remaining = $invoice->balance;
        if ($this->collectAmount > $remaining) {
            $this->addError('collectAmount', 'Payment exceeds invoice balance.');
            return;
        }

        DB::transaction(function () use ($invoice) {
            $previousPayments = \App\Models\Payment::where('invoice_id', $invoice->id)->orderBy('payment_date')->orderBy('created_at')->get();

            $runningBalance = $invoice->grand_total;
            foreach ($previousPayments as $p) {
                $runningBalance -= $p->amount_paid;
            }
            $balanceAfter = $runningBalance - $this->collectAmount;
            // Create payment record
            $payment = \App\Models\Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'amount_paid' => $this->collectAmount,
                'payment_method' => $this->collectMethod,
                'payment_date' => $this->collectDate,
                'reference' => $this->collectReference,
                'notes' => $this->collectNotes,
                'status' => 'collected',
                'balance_after' => $balanceAfter,
            ]);

            // Store payment proofs
            if (!empty($this->paymentProof)) {
                foreach ($this->paymentProof as $file) {
                    $path = $file->store('payment-proofs', 'public');
                    \App\Models\PaymentProof::create([
                        'payment_id' => $payment->id,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            // Update invoice status only
            $newBalance = $invoice->balance - $this->collectAmount;
            $isOverdue = \Carbon\Carbon::parse($invoice->due_date)->lt(now());

            if ($newBalance <= 0) {
                $invoice->status = 'paid';
            } elseif ($isOverdue) {
                $invoice->status = 'overdue';
            } else {
                $invoice->status = 'partially_paid';
            }
            $invoice->save();

            // --- Journal Entry Creation ---
            $journalEntry = JournalEntry::create([
                'journal_no' => 'JE-' . now()->format('YmdHis'),
                'journal_date' => now(),
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'description' => 'Invoice #' . $invoice->invoice_number . ' for Customer ' . $invoice->customer->name,
                'status' => 'posted',
            ]);
            // Get account IDs
            $cashAccount = \App\Models\ChartOfAccount::where('code', '1200')->first();
            $arAccount = \App\Models\ChartOfAccount::where('code', '1100')->first();

            // Journal entries
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'invoice_id' => $invoice->id,
                'account_id' => $cashAccount ? $cashAccount->id : null,
                'debit' => $this->collectAmount,
                'credit' => 0,
                'notes' => "Payment collected for Invoice #{$invoice->invoice_number}",
            ]);
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'invoice_id' => $invoice->id,
                'account_id' => $arAccount ? $arAccount->id : null,
                'debit' => 0,
                'credit' => $this->collectAmount,
                'notes' => "Payment applied to Invoice #{$invoice->invoice_number}",
            ]);
        });

        flash()->success('Payment collected successfully!');

        $this->reset(['collectAmount', 'collectMethod', 'collectReference', 'collectNotes', 'collectDate', 'paymentProof']);

        $this->dispatch('paymentCollected', message: 'Payment collected successfully!');
        $this->dispatch('flux:close-modal', name: 'make-payment-' . $invoiceId);
        Flux::modals()->close();
    }
    public function removeProof($idx)
    {
        $files = $this->paymentProof;
        unset($files[$idx]);
        $this->paymentProof = array_values($files);
    }
    public function getReceivablesProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return \App\Models\JournalLine::with(['journalEntry.invoice.customer', 'journalEntry.invoice.agent'])
            ->where('account_id', $receivableId)
            ->where('debit', '>', 0)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('memo', 'like', '%' . $this->search . '%')
                        ->orWhereHas('journalEntry', function ($je) {
                            $je->where('reference', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('journalEntry.invoice', function ($inv) {
                            $inv->where('invoice_number', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('journalEntry.invoice.customer', function ($cust) {
                            $cust->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            // Status filter
            ->when($this->status, function ($query) {
                $query->whereHas('journalEntry.invoice', function ($inv) {
                    $inv->where('status', $this->status);
                });
            })
            // Date range filter (on invoice issued_date)
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereHas('journalEntry.invoice', function ($inv) {
                    $inv->whereBetween('issued_date', [$this->startDate, $this->endDate]);
                });
            })
            ->when($this->startDate && !$this->endDate, function ($query) {
                $query->whereHas('journalEntry.invoice', function ($inv) {
                    $inv->where('issued_date', '>=', $this->startDate);
                });
            })
            ->when($this->endDate && !$this->startDate, function ($query) {
                $query->whereHas('journalEntry.invoice', function ($inv) {
                    $inv->where('issued_date', '<=', $this->endDate);
                });
            })
            ->latest()
            ->paginate($this->perPage);
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

    #[Title('Receivables')]
    public function with(): array
    {
        return [
            'receivables' => $this->receivables,
            'totalReceivablesCount' => $this->totalReceivablesCount,
            'totalReceivablesAmount' => $this->totalReceivablesAmount,
            'overdueReceivablesCount' => $this->overdueReceivablesCount,
            'overdueReceivablesAmount' => $this->overdueReceivablesAmount,
            'dueThisMonthCount' => $this->dueThisMonthCount,
            'dueThisMonthAmount' => $this->dueThisMonthAmount,
            'collectedThisMonthCount' => $this->collectedThisMonthCount,
            'collectedThisMonthAmount' => $this->collectedThisMonthAmount,
        ];
    }

    public function showInvoice($invoiceId)
    {
        $this->invoice = Invoice::with(['customer', 'items.stock.product'])->find($invoiceId);
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal()
    {
        $this->reset(['invoice', 'showInvoiceModal']);
    }

    public function getTotalReceivablesCountProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return JournalLine::where('account_id', $receivableId)->where('debit', '>', 0)->distinct('journal_entry_id')->count('journal_entry_id');
    }

    public function getTotalReceivablesAmountProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return JournalLine::where('account_id', $receivableId)->where('debit', '>', 0)->with('journalEntry.invoice')->get()->pluck('journalEntry.invoice.grand_total')->filter()->sum();
    }

    public function getOverdueReceivablesCountProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return JournalLine::where('account_id', $receivableId)
            ->where('debit', '>', 0)
            ->whereHas('journalEntry.invoice', function ($q) {
                $q->where('status', 'overdue');
            })
            ->distinct('journal_entry_id')
            ->count('journal_entry_id');
    }

    public function getOverdueReceivablesAmountProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return JournalLine::where('account_id', $receivableId)
            ->where('debit', '>', 0)
            ->whereHas('journalEntry.invoice', function ($q) {
                $q->where('status', 'overdue');
            })
            ->with('journalEntry.invoice')
            ->get()
            ->pluck('journalEntry.invoice.grand_total')
            ->filter()
            ->sum();
    }

    public function getDueThisMonthCountProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return JournalLine::where('account_id', $receivableId)
            ->where('debit', '>', 0)
            ->whereHas('journalEntry.invoice', function ($q) {
                $q->whereMonth('due_date', now()->month)->whereYear('due_date', now()->year);
            })
            ->distinct('journal_entry_id')
            ->count('journal_entry_id');
    }

    public function getDueThisMonthAmountProperty()
    {
        $receivableId = ChartOfAccount::where('code', '1100')->value('id');
        return JournalLine::where('account_id', $receivableId)
            ->where('debit', '>', 0)
            ->whereHas('journalEntry.invoice', function ($q) {
                $q->whereMonth('due_date', now()->month)->whereYear('due_date', now()->year);
            })
            ->with('journalEntry.invoice')
            ->get()
            ->pluck('journalEntry.invoice.grand_total')
            ->filter()
            ->sum();
    }

    public function getCollectedThisMonthCountProperty()
    {
        return \App\Models\Payment::whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->count();
    }

    public function getCollectedThisMonthAmountProperty()
    {
        return \App\Models\Payment::whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount_paid');
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
                        <span
                            class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Receivables</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Account Receivables List</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Manage all your receivables in one place</p>
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


        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select wire:model.live="status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 transition duration-200">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="partially_paid">Partially Paid</option>
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


    <div class="flex justify-between overflow-x-auto mb-8 gap-6">

        <!-- Total AR -->
        <div
            class="flex gap-3 items-center sm:w-4/12 w-full cursor-pointer p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
            <div class="h-14 w-14 rounded-full border-2 border-blue-500 text-blue-500 flex justify-center items-center">
                <!-- Total AR: Banknotes Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a5 5 0 00-10 0v2M5 9h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium">Total AR</h5>
                <p class="text-sm opacity-80">{{ $totalReceivablesCount }} receivables</p>
                <h6 class="text-sm font-semibold">₱{{ number_format($totalReceivablesAmount, 2) }}</h6>
            </div>
        </div>

        <!-- Overdue -->
        <div
            class="flex gap-3 items-center sm:w-4/12 w-full cursor-pointer p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
            <div class="h-14 w-14 rounded-full border-2 border-red-500 text-red-500 flex justify-center items-center">
                <!-- Overdue: Exclamation Circle Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium">Overdue</h5>
                <p class="text-sm opacity-80">{{ $overdueReceivablesCount }} receivables</p>
                <h6 class="text-sm font-semibold">₱{{ number_format($overdueReceivablesAmount, 2) }}</h6>
            </div>
        </div>

        <!-- Due This Month -->
        <div
            class="flex gap-3 items-center sm:w-4/12 w-full cursor-pointer p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
            <div
                class="h-14 w-14 rounded-full border-2 border-yellow-500 text-yellow-500 flex justify-center items-center">
                <!-- Due This Month: Calendar Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium">Due This Month</h5>
                <p class="text-sm opacity-80">{{ $dueThisMonthCount }} receivables</p>
                <h6 class="text-sm font-semibold">₱{{ number_format($dueThisMonthAmount, 2) }}</h6>
            </div>
        </div>

        <!-- Collected This Month -->
        <div
            class="flex gap-3 items-center sm:w-4/12 w-full cursor-pointer p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
            <div
                class="h-14 w-14 rounded-full border-2 border-green-500 text-green-500 flex justify-center items-center">
                <!-- Collected: Check Circle Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2l4-4m5 2a9 9 0 11-18 0a9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium">Collected ({{ now()->format('M') }})</h5>
                <p class="text-sm opacity-80">{{ $collectedThisMonthCount }} payments</p>
                <h6 class="text-sm font-semibold">₱{{ number_format($collectedThisMonthAmount, 2) }}</h6>
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
        @if ($receivables->isEmpty())
            <div class="p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No account receivables found</h3>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Invoice #</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Invoice Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Due Date</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Original Amount</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Amount Paid</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Balance</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($receivables as $line)
                            @php
                                $invoice = $line->journalEntry->invoice ?? null;
                                $originalAmount = $invoice->grand_total ?? 0;
                                $amountPaid = $invoice ? $invoice->payments()->sum('amount_paid') : 0;
                                $balance = $originalAmount - $amountPaid;

                                // Default status
                                $status = 'Pending';

                                if ($invoice) {
                                    if ($invoice->status === 'cancelled') {
                                        $status = 'Cancelled';
                                    } elseif ($invoice->status === 'written_off') {
                                        $status = 'Written Off';
                                    } elseif ($invoice->status === 'overdue') {
                                        $status = 'Overdue';
                                    } elseif ($balance == 0 && $originalAmount > 0) {
                                        $status = 'Paid';
                                    } elseif ($amountPaid > 0 && $balance > 0) {
                                        $status = 'Partially Paid';
                                    } elseif (\Carbon\Carbon::parse($invoice->due_date)->lt(now()) && $balance > 0) {
                                        $status = 'Overdue';
                                    } elseif (\Carbon\Carbon::parse($invoice->due_date)->isToday() && $balance > 0) {
                                        $status = 'Due';
                                    }
                                }
                            @endphp
                            <tr wire:key="receivable-{{ $line->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $invoice->invoice_number ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $invoice->customer->name ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($invoice->issued_date ?? '')->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($invoice->due_date ?? '')->format('M d, Y') }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-gray-100">
                                    ₱{{ number_format($originalAmount, 2) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600 dark:text-green-400 font-semibold">
                                    ₱{{ number_format($amountPaid, 2) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm text-blue-600 dark:text-blue-400 font-semibold">
                                    ₱{{ number_format($balance, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' => in_array(
                                            $status,
                                            ['Pending', 'Due']),
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' =>
                                            $status === 'Paid',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100' =>
                                            $status === 'Partially Paid',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' =>
                                            $status === 'Overdue',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100' => in_array(
                                            $status,
                                            ['Cancelled', 'Written Off']),
                                    ])>
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <flux:dropdown>
                                        <flux:button class="cursor-pointer hover:bg-gray-50 hover:shadow-md"
                                            icon:trailing="chevron-down">Actions</flux:button>
                                        <flux:menu>
                                            <flux:modal.trigger name="view-invoice-{{ $invoice->id ?? '' }}">
                                                <flux:menu.item icon="eye"
                                                    class="hover:bg-gray-50 hover:font-bold cursor-pointer">
                                                    &nbsp;View
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                            @if ($balance > 0)
                                                <flux:modal.trigger name="make-payment-{{ $invoice->id ?? '' }}">
                                                    <flux:menu.item
                                                        class="hover:bg-gray-50 hover:font-bold cursor-pointer"
                                                        icon="credit-card">
                                                        Make Payment
                                                    </flux:menu.item>
                                                </flux:modal.trigger>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>



                                    <flux:modal name="view-invoice-{{ $invoice->id ?? '' }}"
                                        class="sm:max-w-4xl w-full">
                                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <!-- Modal Header -->
                                            <div
                                                class="flex justify-between items-start border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                                                <div>
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        Invoice #{{ $invoice->invoice_number ?? '' }}
                                                    </h3>
                                                    <div
                                                        class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                                        <div
                                                            class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Issued:
                                                            {{ \Carbon\Carbon::parse($invoice->issued_date ?? '')->format('M d, Y') }}
                                                        </div>
                                                        <div
                                                            class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Due:
                                                            {{ \Carbon\Carbon::parse($invoice->due_date ?? '')->format('M d, Y') }}
                                                        </div>
                                                        <div
                                                            class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path
                                                                    d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                            </svg>
                                                            Agent: {{ $invoice->agent->name ?? '' }}
                                                        </div>
                                                        <div
                                                            class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor">
                                                                <path
                                                                    d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                            </svg>
                                                            Customer: {{ $invoice->customer->name ?? '' }}
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <!-- Invoice Details -->
                                            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                                                {{-- <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">BILL FROM</h4>
                    <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">Your Company Name</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">123 Business Street</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">City, State 12345</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone: (123) 456-7890</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email: billing@yourcompany.com</p>
                </div> --}}
                                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                                    <h4
                                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                                        BILL TO</h4>
                                                    <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                                        {{ $invoice->customer->name ?? '' }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $invoice->customer->address ?? '' }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $invoice->customer->phone ?? '' }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $invoice->customer->email ?? '' }}</p>
                                                </div>
                                            </div>

                                            <!-- Invoice Items -->
                                            <div class="mt-6">
                                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                                    INVOICE ITEMS</h4>
                                                <div class="overflow-x-auto">
                                                    <table
                                                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                                            <tr>
                                                                <th
                                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                    Item</th>
                                                                <th
                                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                    Qty</th>
                                                                <th
                                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                    Price</th>
                                                                <th
                                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                    Discount</th>
                                                                <th
                                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                    Tax</th>
                                                                <th
                                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                    Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody
                                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                            @foreach ($invoice->items ?? [] as $item)
                                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                    <td
                                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                                        {{ $item->product_name ?? 'N/A' }}
                                                                        @if (!empty($item->notes))
                                                                            <p
                                                                                class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                                {{ $item->notes }}</p>
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
                                                        <div
                                                            class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                                            <span>Subtotal</span>
                                                            <span>₱{{ number_format($invoice->total_amount ?? 0, 2) }}</span>
                                                        </div>
                                                        @if ($invoice->discount ?? 0 > 0)
                                                            <div
                                                                class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                                                <span>Discount</span>
                                                                <span>-
                                                                    ₱{{ number_format($invoice->discount ?? 0, 2) }}</span>
                                                            </div>
                                                        @endif
                                                        @if ($invoice->tax ?? 0 > 0)
                                                            <div
                                                                class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                                                <span>Tax</span>
                                                                <span>₱{{ number_format($invoice->tax ?? 0, 2) }}</span>
                                                            </div>
                                                        @endif
                                                        <div
                                                            class="flex justify-between py-2 text-lg font-medium text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                                                            <span>Total</span>
                                                            <span>₱{{ number_format($invoice->grand_total ?? 0, 2) }}</span>
                                                        </div>
                                                        <div
                                                            class="flex justify-between py-2 text-sm text-gray-500 dark:text-gray-400">
                                                            <span>Payment Method</span>
                                                            <span>{{ ucfirst(str_replace('_', ' ', $invoice->payment_method ?? 'N/A')) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Invoice Notes -->
                                            @if (!empty($invoice->notes))
                                                <div class="mt-6">
                                                    <h4
                                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                                                        NOTES</h4>
                                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $invoice->notes }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div
                                            class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <flux:modal.close>
                                                <flux:button variant="primary">Close</flux:button>
                                            </flux:modal.close>
                                            <a href="#" target="_blank"
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Download PDF
                                            </a>
                                        </div>
                                    </flux:modal>
                                    <!-- Payment Modal -->
                                    <flux:modal name="make-payment-{{ $invoice->id ?? '' }}"
                                        class="md:max-w-3xl md:w-2/3">
                                        <form wire:submit.prevent="collectPayment({{ $invoice->id ?? '' }})">
                                            <div class="space-y-6">
                                                <!-- Header -->
                                                <div>
                                                    <flux:heading size="lg">Record Payment</flux:heading>
                                                    <flux:text class="mt-2">
                                                        Payment for <b>Invoice
                                                            #{{ $invoice->invoice_number ?? '' }}</b>.
                                                    </flux:text>
                                                </div>

                                                <!-- Invoice Summary -->
                                                <div
                                                    class="p-3 rounded-md bg-gray-50 dark:bg-gray-800 text-sm space-y-1">
                                                    <div class="flex justify-between">
                                                        <span>Invoice Total:</span>
                                                        <span
                                                            class="font-semibold">₱{{ number_format($invoice->grand_total, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Total Paid:</span>
                                                        <span
                                                            class="font-semibold text-green-600">₱{{ number_format($amountPaid, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Balance Due:</span>
                                                        <span
                                                            class="font-semibold text-red-600">₱{{ number_format($balance, 2) }}</span>
                                                    </div>
                                                </div>

                                                <!-- Pay Full Balance Checkbox -->
                                                <div class="flex items-center space-x-2">
                                                    <input type="checkbox" wire:model="payFull"
                                                        id="payFull-{{ $invoice->id }}"
                                                        @change="if($event.target.checked){ $wire.set('collectAmount', {{ $balance }}); }else{ $wire.set('collectAmount', ''); }"
                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                    <label for="payFull-{{ $invoice->id }}"
                                                        class="text-sm text-gray-700 dark:text-gray-300">
                                                        Pay full balance
                                                    </label>
                                                </div>

                                                <!-- Two Column Form -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-4">
                                                        <flux:input label="Amount Received" type="number"
                                                            min="1"
                                                            max="{{ number_format($balance, 2, '.', '') }}"
                                                            step="0.01" wire:model.defer="collectAmount"
                                                            placeholder="Enter amount (max ₱{{ number_format($balance, 2) }})"
                                                            required />
                                                        <flux:input label="Payment Date" type="date"
                                                            wire:model.defer="collectDate" :max="now()->toDateString()"
                                                            required />
                                                    </div>
                                                    <div class="space-y-4">
                                                        <flux:select label="Payment Method"
                                                            wire:model.defer="collectMethod" required>
                                                            <option value="cash">Cash</option>
                                                            <option value="bank_transfer">Bank Transfer</option>
                                                            <option value="credit_card">Credit Card</option>
                                                            <option value="paypal">PayPal</option>
                                                            <option value="gcash">GCash</option>
                                                            <option value="other">Other</option>
                                                        </flux:select>
                                                        <flux:input label="Reference No. (optional)"
                                                            wire:model.defer="collectReference"
                                                            placeholder="Bank ref, OR#, transaction ID, etc." />
                                                    </div>
                                                </div>

                                                <!-- Proof of Payment -->
                                                <flux:input label="Proof of Payment (optional)" type="file"
                                                    wire:model="paymentProof" accept="image/*,.pdf" multiple />
                                                @if ($paymentProof)
                                                    <div class="mt-2 flex flex-wrap gap-4">
                                                        @foreach ($paymentProof as $idx => $file)
                                                            <div class="relative">
                                                                @if (Str::startsWith($file->getMimeType(), 'image/'))
                                                                    <img src="{{ $file->temporaryUrl() }}"
                                                                        class="rounded shadow max-h-32"
                                                                        alt="Proof of Payment">
                                                                @else
                                                                    <span
                                                                        class="text-xs text-gray-500 dark:text-gray-400">
                                                                        {{ $file->getClientOriginalName() }}
                                                                    </span>
                                                                @endif
                                                                <button type="button"
                                                                    wire:click="removeProof({{ $idx }})"
                                                                    class="absolute top-1 right-1 bg-white dark:bg-gray-800 rounded-full p-1 shadow hover:bg-red-100 dark:hover:bg-red-900">
                                                                    <svg class="w-4 h-4 text-red-600" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <flux:textarea label="Notes (optional)"
                                                    wire:model.defer="collectNotes"
                                                    placeholder="Any remarks about this payment" />

                                                <!-- Footer -->
                                                <div class="flex">
                                                    <flux:spacer />
                                                    <flux:button class="hover:bg-black cursor-pointer" type="submit"
                                                        variant="primary">Save Payment
                                                    </flux:button>
                                                </div>

                                                <!-- Collapsible Payment History -->
                                                <!-- Payment History -->
                                                <div x-data="{ open: false }" class="mt-6">
                                                    <!-- Trigger -->
                                                    <button type="button" @click="open = !open"
                                                        class="w-full flex justify-between items-center px-3 py-2 text-sm font-medium border rounded-md bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <span>Payment History</span>
                                                        <svg :class="{ 'rotate-180': open }"
                                                            class="w-4 h-4 text-gray-500 transform transition-transform"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>

                                                    <!-- Content -->
                                                    <div x-show="open" x-collapse class="mt-3">

                                                        @if ($invoice->payments()->count() > 0)
                                                            <div
                                                                class="border rounded-md dark:border-gray-700 overflow-x-auto">
                                                                <table
                                                                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                                                        <tr>
                                                                            <th
                                                                                class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                                                                Amount</th>
                                                                            <th
                                                                                class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                                                                Method</th>
                                                                            <th
                                                                                class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                                                                Reference</th>
                                                                            <th
                                                                                class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                                                                Date</th>
                                                                            <th
                                                                                class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                                                                Balance After</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($invoice->payments()->orderByDesc('payment_date')->orderByDesc('created_at')->get() as $payment)
                                                                            <tr>
                                                                                <td class="px-4 py-2 font-semibold">
                                                                                    ₱{{ number_format($payment->amount_paid, 2) }}
                                                                                </td>
                                                                                <td
                                                                                    class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                                                                    {{ ucfirst($payment->payment_method) }}
                                                                                </td>
                                                                                <td
                                                                                    class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                                                                    {{ $payment->reference ?? 'N/A' }}
                                                                                </td>
                                                                                <td
                                                                                    class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                                                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                                                                </td>
                                                                                <td
                                                                                    class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                                                                    ₱{{ number_format($payment->balance_after, 2) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="text-sm text-gray-500 dark:text-gray-400 p-3">
                                                                No payments recorded yet.
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>
                                        </form>
                                    </flux:modal>


                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                {{ $receivables->links() }}
            </div>
        @endif
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
