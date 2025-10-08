<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\DeliveryNote;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = '';
    public $perPage = 5;
    public $showModal = false;
    public $selectedNote = null;

    public $showDeleteModal = false;
    public $deleteNote = null;

    public function mount()
    {
        $this->perPage = session('dnPerPage', 5);
    }

    public function updatedPerPage($value)
    {
        session(['dnPerPage' => $value]);
        $this->resetPage();
    }

    public function showNote($id)
    {
        $this->selectedNote = DeliveryNote::with(['items', 'salesOrder.customer'])->find($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset(['selectedNote', 'showModal']);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function getDeliveryNotesProperty()
    {
        return DeliveryNote::with(['salesOrder.customer'])
            ->when($this->search, function ($q) {
                $q->where('delivery_note_number', 'like', "%{$this->search}%")->orWhereHas('salesOrder', function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate($this->perPage);
    }

    public function confirmDelete($id)
    {
        $this->deleteNote = DeliveryNote::find($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->reset(['showDeleteModal', 'deleteNote']);
    }

    public function deleteNoteConfirmed()
    {
        if ($this->deleteNote) {
            $this->deleteNote->delete();
            session()->flash('message', 'Delivery Note deleted successfully!');
        }
        $this->cancelDelete();
        $this->resetPage();
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
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">
                            Delivery Notes
                        </span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>



    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Delivery Notes</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Browse all Delivery Notes, track their status, and take quick actions like printing or viewing details.
            </p>
        </div>
        <div>
            <a href="{{ route('delivery-notes.create') }}"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Delivery Note
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Delivery Notes -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="h-14 w-14 rounded-full border-2 border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Total DNs</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\DeliveryNote::count() }} delivery notes
                </p>
            </div>
        </div>
        <!-- Fulfilled -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="h-14 w-14 rounded-full border-2 border-green-500 bg-green-50 dark:bg-green-900/20 text-green-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Fulfilled</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\DeliveryNote::where('status', 'fulfilled')->count() }} fulfilled
                </p>
            </div>
        </div>
        <!-- Partially Fulfilled -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="h-14 w-14 rounded-full border-2 border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Partially Fulfilled</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\DeliveryNote::where('status', 'partially fulfilled')->count() }} partial
                </p>
            </div>
        </div>
        <!-- Draft/Other -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="h-14 w-14 rounded-full border-2 border-gray-500 bg-gray-50 dark:bg-gray-700/20 text-gray-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Draft/Other</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\DeliveryNote::whereNotIn('status', ['fulfilled', 'partially fulfilled'])->count() }}
                    others
                </p>
            </div>
        </div>
    </div>

    <!-- Filters/Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search for Delivery Notes..."
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
        <div>
            <label class="text-sm text-gray-600 dark:text-gray-300 mr-2">Show</label>
            <select wire:model="perPage"
                class="px-2 py-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-sm text-gray-600 dark:text-gray-300 ml-1">per page</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if ($this->deliveryNotes->isEmpty())
            <div class="p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No Delivery Notes found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new Delivery Note.
                </p>
                <div class="mt-6">
                    <a href="{{ route('delivery-notes.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Delivery Note
                    </a>
                </div>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DN #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SO #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($this->deliveryNotes as $dn)
                        <tr>
                            <td class="px-6 py-4">{{ $dn->delivery_note_number }}</td>
                            <td class="px-6 py-4">{{ $dn->salesOrder->order_number ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $dn->salesOrder->customer->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $dn->salesOrder->customer->address ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $dn->created_at ? $dn->created_at->format('M d, Y') : '-' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if ($dn->status === 'fulfilled') bg-green-100 text-green-800
                                    @elseif($dn->status === 'partially fulfilled') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($dn->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
    <div class="flex gap-2">
        <a href="{{ route('delivery-notes.show', $dn->id) }}"
           class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium transition"
           title="View">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            View
        </a>
        <a href="{{ route('delivery-notes.print', $dn->id) }}"
           class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded text-xs font-medium transition"
           title="Print">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                <rect width="12" height="8" x="6" y="14" rx="2" />
            </svg>
            Print
        </a>
        <button wire:click="confirmDelete({{ $dn->id }})"
            class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium transition"
            title="Delete">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Delete
        </button>
    </div>
</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-3">{{ $this->deliveryNotes->links() }}</div>
        @endif
    </div>

    <!-- Delete Modal (optional) -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Delete Delivery Note?</h2>
                <p class="mb-4">Are you sure you want to delete this delivery note?</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="deleteNoteConfirmed"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
