<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\SalesOrder;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = '';
    public $perPage = 10;
    public $showModal = false;
    public $selectedOrder = null;

    public $showDeleteModal = false;
    public $openChecklist = false;
    public $deleteOrder = null;

    public $statusUpdate = '';
    public $sendToCompanyOwner = false;
    public $sendToSupplier = false;
    public function mount()
    {
        $this->perPage = session('prPerPage', 10);
    }

    public function updatedPerPage($value)
    {
        session(['prPerPage' => $value]);
        $this->resetPage();
    }

    public function showOrder($id)
    {
        $this->selectedOrder = PurchaseOrder::with(['items', 'purchaser', 'approved_by_user'])->find($id);
        $this->statusUpdate = $this->selectedOrder->status ?? '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset(['selectedOrder', 'showModal']);
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }
    public function closeOrder($id)
    {
        $so = \App\Models\SalesOrder::findOrFail($id);

        if ($so->status !== 'confirmed') {
            flash()->error('Only confirmed sales orders can be closed.');
            return;
        }

        $so->status = 'closed';
        $so->save();

        flash()->success('Sales Order closed!');
        $this->dispatch('refresh');
    }
    public function getSalesOrdersProperty()
    {
        return SalesOrder::with(['customer', 'agent'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate($this->perPage);
    }
    public function confirmDelete($id)
    {
        $this->deleteOrder = \App\Models\SalesOrder::find($id);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->reset(['showDeleteModal', 'deleteOrder']);
    }

    public function deleteOrderConfirmed()
    {
        if ($this->deleteOrder) {
            $this->deleteOrder->delete(); // uses softDeletes
            session()->flash('message', 'Sales Orders deleted successfully!');
        }
        $this->cancelDelete();
        $this->resetPage();
    }

    public function updateStatus($id)
    {
        $order = \App\Models\PurchaseOrder::findOrFail($id);
        $this->validate([
            'selectedOrder.status' => 'required|in:pending,partially delivered,delivered,closed,cancelled',
        ]);
        $order->status = $this->statusUpdate;
        $order->save();
        $this->selectedOrder = $order->fresh(['items', 'purchaser', 'supplier']);
        session()->flash('message', 'Status updated!');
    }
    public function sendToChecklist($poId)
    {
        $so = \App\Models\PurchaseOrder::findOrFail($poId);

        $sentTo = [];
        if ($this->sendToSupplier && $so->supplier && $so->supplier->email) {
            \Mail::to($so->supplier->email)->send(new \App\Mail\PurchaseOrderMail($so));
            $sentTo[] = 'Supplier';
        }
        if ($this->sendToCompanyOwner) {
            // Add your logic for company owner here
            $sentTo[] = 'Company Owner';
        }

        flash()->success('Sales Orders sent to: ' . implode(', ', $sentTo));
        $this->openChecklist = false;
        $this->sendToCompanyOwner = false;
        $this->sendToSupplier = false;
    }
    public function confirmOrder($id)
    {
        $so = \App\Models\SalesOrder::findOrFail($id);

        if ($so->status !== 'quotation') {
            flash()->error('Only quotations can be confirmed.');
            return;
        }

        $so->status = 'confirmed';
        $so->save();

        flash()->success('Sales Order confirmed!');
        $this->dispatch('refresh'); // or $this->emit('refresh') if using Livewire 2
    }
}; ?>

<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Sales Orders</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Browse all Sales Orders, track their status, and take quick actions like editing or viewing details.
            </p>
        </div>
        <div>
            <a href="{{ route('sales-orders.create') }}"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Sales Orders
            </a>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

        <!-- Total Sales Orders -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-blue-500 bg-gray-50 dark:bg-blue-900/20 text-blue-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Total SOs</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ \App\Models\SalesOrder::count() }} sales orders
                </p>
            </div>
        </div>

        <!-- Quotations -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-yellow-500 bg-gray-50 dark:bg-yellow-900/20 text-yellow-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Quotations</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\SalesOrder::where('status', 'quotation')->count() }} quotations
                </p>
            </div>
        </div>

        <!-- Confirmed/Approved -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-indigo-500 bg-gray-50 dark:bg-indigo-900/20 text-indigo-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Confirmed</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\SalesOrder::where('status', 'confirmed')->count() }} confirmed
                </p>
            </div>
        </div>

        <!-- Delivered -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-green-500 bg-gray-50 dark:bg-green-900/20 text-green-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Delivered</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\SalesOrder::where('status', 'delivered')->count() }} delivered
                </p>
            </div>
        </div>

        <!-- Closed/Completed -->
        <div
            class="flex gap-3 items-center p-5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <div
                class="relative h-14 w-14 rounded-full border-2 border-gray-500 bg-gray-50 dark:bg-gray-700/20 text-gray-500 flex justify-center items-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <h5 class="text-base font-medium text-gray-800 dark:text-gray-100">Closed</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ \App\Models\SalesOrder::where('status', 'closed')->count() }} closed
                </p>
            </div>
        </div>
    </div>

    <!-- Filters/Search for PO -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <!-- Search -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search for Sales Orders..."
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
        @if ($this->salesOrders->isEmpty())
            <div class="p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No Sales Orders found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new Sales Orders.
                </p>
                <div class="mt-6">
                    <a href="{{ route('sales-orders.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Sales Orders
                    </a>
                </div>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SO #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($this->salesOrders as $so)
                        <tr>
                            <td class="px-6 py-4">{{ $so->order_number }}</td>
                            <td class="px-6 py-4">{{ $so->customer->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $so->agent->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $so->created_at ? $so->created_at->format('M d, Y') : '-' }}</td>
                            <td class="px-6 py-4">₱ {{ number_format($so->items->sum(fn($item) => $item->total), 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold
                @if ($so->status === 'quotation') bg-yellow-100 text-yellow-800
                @elseif($so->status === 'confirmed') bg-green-100 text-green-800
                @elseif($so->status === 'closed') bg-gray-100 text-gray-800
                @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($so->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:dropdown>
                                    <flux:button class="cursor-pointer" icon="ellipsis-vertical" variant="ghost"
                                        size="sm" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('sales-orders.show', $so->id) }}"
                                            icon="eye">
                                            View
                                        </flux:menu.item>

                                        @if ($so->status === 'quotation')
                                            <flux:menu.item wire:click="confirmOrder({{ $so->id }})"
                                                icon="check" class="text-green-600 cursor-pointer">
                                                Confirm
                                            </flux:menu.item>
                                        @endif
                                        @if ($so->status === 'confirmed')
                                            <flux:menu.item
                                                href="{{ route('delivery-notes.create', ['sales_order_id' => $so->id]) }}"
                                                icon="truck">
                                                Create Delivery Note
                                            </flux:menu.item>
                                            <flux:menu.item wire:click="closeOrder({{ $so->id }})"
                                                icon="lock-closed" class="text-gray-600 cursor-pointer">
                                                Close
                                            </flux:menu.item>
                                        @endif
                                        <flux:menu.item href="{{ route('sales-order.stream-pdf', $so->id) }}"
                                            icon="printer">
                                            Print
                                        </flux:menu.item>
                                        <flux:menu.item wire:click="confirmDelete({{ $so->id }})"
                                            icon="trash" class="text-red-600">
                                            Delete
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-3">{{ $this->salesOrders->links() }}</div>
        @endif
    </div>

    <!-- Modal for viewing PR -->
    <div x-cloak x-data="{ show: @entangle('showModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
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
                                Request #{{ $selectedOrder->po_number ?? '' }}
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
                                    Date Ordered:
                                    {{ \Carbon\Carbon::parse($selectedOrder->created_at ?? '')->format('M d, Y') }}
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path
                                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                    Ordered by: {{ $selectedOrder->purchaser->name ?? '' }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <span @class([
                                'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' =>
                                    isset($selectedOrder) && $selectedOrder->status === 'pending',
                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100' =>
                                    isset($selectedOrder) &&
                                    $selectedOrder->status === 'partially delivered',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' =>
                                    isset($selectedOrder) && $selectedOrder->status === 'delivered',
                                'bg-red-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100' =>
                                    isset($selectedOrder) && $selectedOrder->status === 'closed',
                                'bg-gray-100 text-red-800 dark:bg-red-700 dark:text-red-100' =>
                                    isset($selectedOrder) && $selectedOrder->status === 'cancelled',
                            ])>
                                {{ isset($selectedOrder) ? ucfirst($selectedOrder->status) : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-3">

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">SUPPLIER</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ ucfirst($selectedOrder->supplier->trade_name ?? '') }}
                            </p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">ORDER TYPE</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ ucfirst($selectedOrder->order_type ?? '') }}
                            </p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">PAYMENT TERMS</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ ucfirst($selectedOrder->payment_terms ?? '') }}
                            </p>
                        </div>

                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-1">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">REMARKS</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ $selectedOrder->remarks ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">PURCHASRE ORDER DETAILS
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Item/s
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Qty
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Estimated Cost
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($selectedOrder->items ?? [] as $item)
                                        <tr>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                ({{ $item->product->product_code ?? 'N/A' }})
                                                {{ $item->product->name ?? 'N/A' }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $item->product->description ?? 'N/A' }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item->quantity }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                ₱ {{ number_format($item->price, 2) }}
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
                                    <span>
                                        ₱
                                        {{ $selectedOrder && $selectedOrder->items
                                            ? number_format($selectedOrder->items->sum(fn($item) => $item->price), 2)
                                            : '0.00' }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between py-2 text-lg font-medium text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                                    <span>Total</span>
                                    <span>
                                        ₱
                                        {{ $selectedOrder && $selectedOrder->items
                                            ? number_format($selectedOrder->items->sum(fn($item) => $item->price), 2)
                                            : '0.00' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($selectedOrder)
                    <div
                        class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse items-center">
                        <button type="button" wire:click="closeModal"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                        <a href="#" target="_blank"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Download PDF
                        </a>
                        @can('sales-orders.update-status')
                            <div x-data="{ showStatusDropdown: false }" class="flex items-center gap-2 mr-auto">
                                <template x-if="!showStatusDropdown">
                                    <button type="button" @click="showStatusDropdown = true"
                                        class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100 font-medium hover:bg-yellow-200 dark:hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:w-auto sm:text-sm transition">
                                        Update Status
                                    </button>
                                </template>
                                <template x-if="showStatusDropdown">
                                    <form wire:submit.prevent="updateStatus({{ $selectedOrder->id }})"
                                        class="flex items-center gap-2 animate-fade-in"
                                        @click.away="showStatusDropdown = false">
                                        <select wire:model.defer="statusUpdate"
                                            class="rounded-md border px-3 py-2 text-sm font-semibold shadow focus:ring-2 focus:ring-yellow-400 transition
                                                @if ($statusUpdate === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100
                                                @elseif($statusUpdate === 'partially delivered') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100
                                                @elseif($statusUpdate === 'delivered') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100
                                                @elseif($statusUpdate === 'closed') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100 @endif
                                            ">
                                            <option value="pending"
                                                class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100">
                                                Pending</option>
                                            <option value="partially delivered"
                                                class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100">
                                                Partially Delivered</option>
                                            <option value="delivered"
                                                class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                                Delivered</option>
                                            <option value="closed"
                                                class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
                                                Closed</option>
                                            <option value="cancelled"
                                                class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">Cancelled
                                            </option>
                                        </select>
                                        <button type="submit"
                                            class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                                            Save
                                        </button>
                                        <button type="button" @click="showStatusDropdown = false"
                                            class="px-3 py-2 rounded-md bg-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-300 transition">
                                            Cancel
                                        </button>
                                    </form>
                                </template>
                            </div>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>


</div>
