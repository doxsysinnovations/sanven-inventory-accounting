<?php

use Livewire\Volt\Component;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public ChartOfAccount $chartOfAccount;
    public Collection $ledgerEntries;
    public string $startDate = '';
    public string $endDate = '';

    public function mount(ChartOfAccount $chartOfAccount): void
    {
        $this->chartOfAccount = $chartOfAccount;
        $this->loadLedgerEntries();
    }

    public function loadLedgerEntries(): void
    {
        $query = $this->chartOfAccount->journalLines()->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')->select('journal_lines.*')->orderBy('journal_entries.journal_date')->orderBy('journal_entries.id')->orderBy('journal_lines.id');

        if ($this->startDate) {
            $query->where('journal_entries.journal_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where('journal_entries.journal_date', '<=', $this->endDate);
        }

        $this->ledgerEntries = $query->get();
    }

    public function updatedStartDate(): void
    {
        $this->loadLedgerEntries();
    }

    public function updatedEndDate(): void
    {
        $this->loadLedgerEntries();
    }

    public function calculateRunningBalance()
    {
        $balance = 0;
        $entriesWithBalance = [];

        foreach ($this->ledgerEntries as $entry) {
            if ($this->chartOfAccount->normal_balance === 'debit') {
                $balance += $entry->debit - $entry->credit;
            } else {
                $balance += $entry->credit - $entry->debit;
            }

            $entriesWithBalance[] = [
                'entry' => $entry,
                'running_balance' => $balance,
            ];
        }

        return $entriesWithBalance;
    }

    public function getOpeningBalance()
    {
        $query = $this->chartOfAccount->journalLines()->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id');

        if ($this->startDate) {
            $query->where('journal_entries.journal_date', '<', $this->startDate);
        }

        $debits = (clone $query)->sum('debit');
        $credits = (clone $query)->sum('credit');

        return $this->chartOfAccount->normal_balance === 'debit' ? $debits - $credits : $credits - $debits;
    }
}; ?>

<div>
    <div class="max-w-12xl sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Account Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Account Code</p>
                        <p class="text-lg font-semibold">{{ $chartOfAccount->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Account Name</p>
                        <p class="text-lg font-semibold">{{ $chartOfAccount->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Type</p>
                        <p class="text-lg font-semibold">{{ ucfirst($chartOfAccount->type) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Current Balance</p>
                        <p
                            class="text-lg font-semibold {{ $chartOfAccount->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($chartOfAccount->balance, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Date Filter -->
                <div class="mb-6 p-4 bg-white border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-medium mb-4">Filter by Date Range</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700">Start
                                Date</label>
                            <input type="date" id="startDate" wire:model.live="startDate"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="endDate" wire:model.live="endDate"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex items-end">
                            <button type="button" wire:click="$set('startDate', '') && $set('endDate', '')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table -->
                <div class="overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Journal No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Debit</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Credit</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Opening Balance -->
                            @php
                                $openingBalance = $this->getOpeningBalance();
                                $runningBalance = $openingBalance;
                            @endphp
                            <tr class="bg-blue-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->subDay()->format('Y-m-d') : 'Beginning' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Opening
                                    Balance</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">-</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right {{ $openingBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($openingBalance, 2) }}
                                </td>
                            </tr>

                            <!-- Ledger Entries -->
                            @foreach ($this->calculateRunningBalance() as $entryWithBalance)
                                @php
                                    $entry = $entryWithBalance['entry'];
                                    $runningBalance = $entryWithBalance['running_balance'];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($entry->journalEntry->journal_date)->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $entry->journalEntry->journal_no ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $entry->journalEntry->description ?? ($entry->memo ?? 'No description') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        @if ($entry->debit > 0)
                                            {{ number_format($entry->debit, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        @if ($entry->credit > 0)
                                            {{ number_format($entry->credit, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($runningBalance, 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            @if ($ledgerEntries->isEmpty())
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No ledger entries found for the selected period.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                @if (!$ledgerEntries->isEmpty())
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Debits</p>
                            <p class="text-lg font-semibold text-green-600">
                                {{ number_format($ledgerEntries->sum('debit'), 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Credits</p>
                            <p class="text-lg font-semibold text-red-600">
                                {{ number_format($ledgerEntries->sum('credit'), 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Ending Balance</p>
                            <p
                                class="text-lg font-semibold {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($runningBalance, 2) }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
