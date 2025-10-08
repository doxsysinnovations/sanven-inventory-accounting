<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

new class extends Component {
    public $invoice;
    public $collectAmount;
    public $collectMethod = 'cash';
    public $collectReference;
    public $collectNotes;
    public $collectDate;
    public $paymentProof = [];
    public $payFull = false;

    public function mount($invoice)
    {
        $this->invoice = $invoice;
        $this->collectDate = now()->toDateString();
    }

    public function collectPayment()
    {
        $this->validate([
            'collectAmount' => 'required|numeric|min:1',
            'collectMethod' => 'required|string',
            'collectReference' => 'nullable|string|max:255',
            'collectNotes' => 'nullable|string',
            'collectDate' => 'required|date',
            'paymentProof.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $invoice = $this->invoice;
        $amountPaid = $invoice->payments()->sum('amount_paid');
        $remaining = $invoice->grand_total - $amountPaid;
        if ($this->collectAmount > $remaining) {
            $this->addError('collectAmount', 'Payment exceeds invoice balance.');
            return;
        }

        DB::transaction(function () use ($invoice) {
            $payment = \App\Models\Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'amount_paid' => $this->collectAmount,
                'payment_method' => $this->collectMethod,
                'payment_date' => $this->collectDate,
                'reference' => $this->collectReference,
                'notes' => $this->collectNotes,
                'status' => 'collected',
            ]);

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

            $amountPaid = $invoice->payments()->sum('amount_paid') + $this->collectAmount;
            $invoice->status = $invoice->grand_total - $amountPaid <= 0 ? 'paid' : 'partially_paid';
            $invoice->save();

            $journalEntry = \App\Models\JournalEntry::create([
                'journal_no' => 'JE-' . now()->format('YmdHis'),
                'journal_date' => now(),
                'reference_type' => \App\Models\Invoice::class,
                'reference_id' => $invoice->id,
                'description' => 'Invoice #' . $invoice->invoice_number . ' for Customer ' . $invoice->customer->name,
                'status' => 'posted',
            ]);
            $cashAccount = \App\Models\ChartOfAccount::where('code', '1200')->first();
            $arAccount = \App\Models\ChartOfAccount::where('code', '1100')->first();

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

        $this->reset(['collectAmount', 'collectMethod', 'collectReference', 'collectNotes', 'collectDate', 'paymentProof', 'payFull']);
        Flux::modals()->close();
        // $this->dispatch('paymentCollected', message: 'Payment collected successfully!');
     $this->js(<<<'JS'
    window.dispatchEvent(new CustomEvent('paymentCollected'));
JS);
    }

    public function removeProof($idx)
    {
        unset($this->paymentProof[$idx]);
        $this->paymentProof = array_values($this->paymentProof);
    }
};
?>

<div>
    <flux:modal name="make-payment-{{ $invoice->id ?? '' }}" class="md:max-w-3xl md:w-2/3">
        <form wire:submit.prevent="collectPayment">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Record Payment</flux:heading>
                    <flux:text class="mt-2">
                        Payment for <b>Invoice #{{ $invoice->invoice_number ?? '' }}</b>.
                    </flux:text>
                </div>

                @php
                    $amountPaid = $invoice->payments()->sum('amount_paid');
                    $balance = $invoice->grand_total - $amountPaid;
                    $payments = $invoice->payments;
                @endphp

                <div class="p-3 rounded-md bg-gray-50 dark:bg-gray-800 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Invoice Total:</span>
                        <span class="font-semibold">₱{{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Paid:</span>
                        <span class="font-semibold text-green-600">₱{{ number_format($amountPaid, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Balance Due:</span>
                        <span class="font-semibold text-red-600">₱{{ number_format($balance, 2) }}</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" wire:model="payFull" id="payFull-{{ $invoice->id }}"
                        @change="if($event.target.checked){ $wire.set('collectAmount', {{ $balance }}); }else{ $wire.set('collectAmount', ''); }"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="payFull-{{ $invoice->id }}" class="text-sm text-gray-700 dark:text-gray-300">
                        Pay full balance
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <flux:input label="Amount Received" type="number" min="1"
                            max="{{ number_format($balance, 2, '.', '') }}" step="0.01"
                            wire:model.defer="collectAmount"
                            placeholder="Enter amount (max ₱{{ number_format($balance, 2) }})" required />
                        <flux:input label="Payment Date" type="date" wire:model.defer="collectDate"
                            value="{{ now()->format('Y-m-d') }}" required />
                    </div>
                    <div class="space-y-4">
                        <flux:select label="Payment Method" wire:model.defer="collectMethod" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="gcash">GCash</option>
                            <option value="other">Other</option>
                        </flux:select>
                        <flux:input label="Reference No. (optional)" wire:model.defer="collectReference"
                            placeholder="Bank ref, OR#, transaction ID, etc." />
                    </div>
                </div>

                <flux:input label="Proof of Payment (optional)" type="file" wire:model="paymentProof"
                    accept="image/*,.pdf" multiple />
                @if ($paymentProof)
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach ($paymentProof as $idx => $file)
                            <div class="relative">
                                @if (Str::startsWith($file->getMimeType(), 'image/'))
                                    <img src="{{ $file->temporaryUrl() }}" class="rounded shadow max-h-32"
                                        alt="Proof of Payment">
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $file->getClientOriginalName() }}
                                    </span>
                                @endif
                                <button type="button" wire:click="removeProof({{ $idx }})"
                                    class="absolute top-1 right-1 bg-white dark:bg-gray-800 rounded-full p-1 shadow hover:bg-red-100 dark:hover:bg-red-900">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <flux:textarea label="Notes (optional)" wire:model.defer="collectNotes"
                    placeholder="Any remarks about this payment" />

                <div class="flex">
                    <flux:spacer />
                    <flux:button class="hover:bg-black cursor-pointer" type="submit" variant="primary">Save Payment
                    </flux:button>
                </div>

                <div x-data="{ open: false }" class="mt-6">
                    <button type="button" @click="open = !open"
                        class="w-full flex justify-between items-center px-3 py-2 text-sm font-medium border rounded-md bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span>Payment History</span>
                        <svg :class="{ 'rotate-180': open }"
                            class="w-4 h-4 text-gray-500 transform transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="mt-3">
                        @if (!empty($payments) && $payments->count() > 0)
                            <div class="border rounded-md divide-y dark:border-gray-700">
                                @foreach ($payments as $payment)
                                    <div class="p-3 flex justify-between text-sm">
                                        <div>
                                            <div class="font-semibold">
                                                ₱{{ number_format($payment->amount_paid, 2) }}
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400">
                                                {{ ucfirst($payment->payment_method) }}
                                                · Ref: {{ $payment->reference ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                        </div>
                                    </div>
                                @endforeach
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
</div>
