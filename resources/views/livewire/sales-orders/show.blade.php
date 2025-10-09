<?php

use Livewire\Volt\Component;
use App\Models\SalesOrder;

new class extends Component {
    public SalesOrder $salesOrder;
    public bool $editMode = false;
    public array $items = [];
    public $order_date, $requested_delivery_date, $payment_terms, $payment_method, $notes;

    public function mount(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'agent', 'items.product']);
        $this->salesOrder = $salesOrder;
        $this->order_date = is_string($salesOrder->order_date) ? $salesOrder->order_date : $salesOrder->order_date?->format('Y-m-d');
        $this->requested_delivery_date = is_string($salesOrder->requested_delivery_date) ? $salesOrder->requested_delivery_date : $salesOrder->requested_delivery_date?->format('Y-m-d');
        $this->payment_terms = $salesOrder->payment_terms;
        $this->payment_method = $salesOrder->payment_method;
        $this->notes = $salesOrder->notes;
        $this->items = $salesOrder->items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product?->name ?? ($item->name ?? 'N/A'),
                    'strength' => $item->strength ?? ($item->product?->strength ?? 'N/A'),
                    'unit' => $item->unit ?? ($item->product?->unit ?? 'N/A'),
                    'type' => $item->type ?? ($item->product?->type ?? 'N/A'),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'vat' => $item->vat,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    public function enableEdit()
    {
        if ($this->salesOrder->status !== 'confirmed') {
            $this->editMode = true;
        }
    }

    public function saveAll()
    {
        $this->validate([
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $this->salesOrder->order_date = $this->order_date;
        $this->salesOrder->requested_delivery_date = $this->requested_delivery_date;
        $this->salesOrder->payment_terms = $this->payment_terms;
        $this->salesOrder->payment_method = $this->payment_method;
        $this->salesOrder->notes = $this->notes;
        $this->salesOrder->save();

        foreach ($this->items as $i => $item) {
            $orderItem = $this->salesOrder->items[$i];
            $orderItem->quantity = $item['quantity'];
            $orderItem->save();
        }

        // Recalculate totals (VAT is already included in price)
        $this->salesOrder->refresh(); // get latest items
        $subtotal = $this->salesOrder->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $discount = $this->salesOrder->items->sum('discount');
        $tax = $this->salesOrder->items->sum('vat');
        $grand_total = $subtotal - $discount; // Do NOT add $tax if VAT is included

        $this->salesOrder->subtotal = $subtotal;
        $this->salesOrder->discount = $discount;
        $this->salesOrder->tax = $tax;
        $this->salesOrder->grand_total = $grand_total;
        $this->salesOrder->save();

        $this->editMode = false;
        session()->flash('success', 'Order and items updated!');
    }
    public function removeItem($index)
    {
        $orderItem = $this->salesOrder->items[$index];
        $orderItem->delete();
        $this->salesOrder->refresh();
        $this->items = $this->salesOrder->items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product?->name ?? ($item->name ?? 'N/A'),
                    'strength' => $item->strength ?? ($item->product?->strength ?? 'N/A'),
                    'unit' => $item->unit ?? ($item->product?->unit ?? 'N/A'),
                    'type' => $item->type ?? ($item->product?->type ?? 'N/A'),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'vat' => $item->vat,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }
};
?>

<div>

    {{-- Heading and Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 rounded-t-lg gap-2">
        <div>
            <h3 class="font-extrabold text-lg lg:text-xl dark:text-white tracking-tight">
                Sales Order Details
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                Review all information, customer details, and product breakdown for this sales order.
            </p>
        </div>
        <div class="flex gap-2 mt-3 sm:mt-0">
            <flux:button href="{{ route('sales-orders') }}" icon="arrow-left" variant="outline" size="sm">
                Back
            </flux:button>
            @if ($salesOrder->status !== 'confirmed' && !$editMode)
                <flux:button wire:click="enableEdit" icon="pencil-square" variant="primary" size="sm">
                    Edit
                </flux:button>
            @endif
            @if ($editMode)
                <flux:button wire:click="saveAll" icon="check" variant="primary" size="sm">
                    Save
                </flux:button>
                <flux:button type="button" wire:click="$set('editMode', false)" icon="x-mark" variant="outline"
                    size="sm">
                    Cancel
                </flux:button>
            @endif
            <flux:button href="{{ route('sales-order.stream-pdf', $salesOrder->id) }}" icon="printer" variant="primary"
                size="sm">
                Print Sales Order
            </flux:button>
            <flux:dropdown>
                <flux:button icon="ellipsis-vertical" variant="outline" size="sm" />
                <flux:menu>
                    <flux:menu.item wire:click="cancelOrder({{ $salesOrder->id }})" icon="x-circle"
                        class="text-red-600">
                        Cancel Sales Order
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- Customer Info --}}
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg mb-6">
        <h4 class="font-semibold text-md text-blue-800 dark:text-blue-200 mb-4">Customer Information</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-base text-sm">
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Name:</span> <span
                    class="text-gray-900 dark:text-white">{{ $salesOrder->customer?->name ?? 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> <span
                    class="text-gray-900 dark:text-white">{{ $salesOrder->customer?->email ?? 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Phone:</span> <span
                    class="text-gray-900 dark:text-white">{{ $salesOrder->customer?->phone ?? 'N/A' }}</span></div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Address:</span> <span
                    class="text-gray-900 dark:text-white">{{ $salesOrder->customer?->address ?? 'N/A' }}</span></div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 space-y-10 rounded-lg shadow-lg">

        {{-- Sales Order Info --}}
        <form wire:submit.prevent="saveAll">
            <div>
                <h4 class="font-semibold text-xl text-blue-800 dark:text-blue-200 mb-4">Order Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-base">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">SO Number:</span>
                        <span class="text-gray-900 dark:text-white">{{ $salesOrder->order_number ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Order Date:</span>
                        @if ($editMode)
                            <input type="date" wire:model.defer="order_date" class="form-input w-full" />
                        @else
                            <span class="text-gray-900 dark:text-white">{{ $salesOrder->order_date ?? 'N/A' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                        <span
                            class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                            @if ($salesOrder->status === 'confirmed') bg-green-100 text-green-800
                            @elseif($salesOrder->status === 'quotation') bg-yellow-100 text-yellow-800
                            @elseif($salesOrder->status === 'closed') bg-gray-100 text-gray-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ ucfirst($salesOrder->status ?? 'N/A') }}
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Agent:</span>
                        <span class="text-gray-900 dark:text-white">{{ $salesOrder->agent?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Requested Delivery:</span>
                        @if ($editMode)
                            <input type="date" wire:model.defer="requested_delivery_date"
                                class="form-input w-full" />
                        @else
                            <span class="text-gray-900 dark:text-white">
                                {{ $salesOrder->requested_delivery_date ? \Carbon\Carbon::parse($salesOrder->requested_delivery_date)->format('F j, Y') : 'N/A' }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Payment Terms:</span>
                        @if ($editMode)
                            <input type="text" wire:model.defer="payment_terms" class="form-input w-full" />
                        @else
                            <span
                                class="text-gray-900 dark:text-white">{{ $salesOrder->payment_terms ?? 'N/A' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Payment Method:</span>
                        @if ($editMode)
                            <input type="text" wire:model.defer="payment_method" class="form-input w-full" />
                        @else
                            <span
                                class="text-gray-900 dark:text-white">{{ $salesOrder->payment_method ?? 'N/A' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Notes:</span>
                        @if ($editMode)
                            <textarea wire:model.defer="notes" class="form-input w-full"></textarea>
                        @else
                            <span class="text-gray-900 dark:text-white">{{ $salesOrder->notes ?? 'N/A' }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="mt-10">
                <h4 class="font-semibold text-xl text-blue-800 dark:text-blue-200 mb-4">Products</h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-base">
                        <thead class="bg-blue-50 dark:bg-blue-900">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-blue-900 dark:text-blue-200">Product
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-blue-900 dark:text-blue-200">Strength
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-blue-900 dark:text-blue-200">Unit</th>
                                <th class="px-4 py-3 text-left font-semibold text-blue-900 dark:text-blue-200">Type</th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">Qty</th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">Price
                                </th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">Discount
                                </th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">VAT</th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">Total
                                </th>
                                @if ($editMode)
                                    <th class="px-4 py-3"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $item)
                                <tr class="border-t dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-800">
                                    <td class="px-4 py-2">{{ $item['product_name'] }}</td>
                                    <td class="px-4 py-2">{{ $item['strength'] }}</td>
                                    <td class="px-4 py-2">{{ $item['unit'] }}</td>
                                    <td class="px-4 py-2">{{ $item['type'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if ($editMode)
                                            <input type="number" min="1"
                                                wire:model.defer="items.{{ $i }}.quantity"
                                                class="form-input w-20" />
                                        @else
                                            {{ $item['quantity'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right">₱{{ number_format($item['price'] ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-right">₱{{ number_format($item['discount'] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">₱{{ number_format($item['vat'] ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">
                                        ₱{{ number_format($item['total'] ?? 0, 2) }}</td>
                                    @if ($editMode)
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" wire:click="removeItem({{ $i }})"
                                                class="text-red-600 hover:underline">Remove</button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Save/Cancel Buttons --}}
            @if ($editMode)
                <div class="mt-4 flex gap-2">
                    <flux:button type="submit" icon="check" variant="primary" size="sm">
                        Save
                    </flux:button>
                    <flux:button type="button" wire:click="$set('editMode', false)" icon="x-mark"
                        variant="outline" size="sm">
                        Cancel
                    </flux:button>
                </div>
            @endif
        </form>

        {{-- Totals --}}
        <div class="text-right space-y-1 mt-8">
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Subtotal:</span> <span
                    class="text-gray-900 dark:text-white">{{ number_format($salesOrder->subtotal ?? 0, 2) }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Discount:</span> <span
                    class="text-gray-900 dark:text-white">{{ number_format($salesOrder->discount ?? 0, 2) }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Tax:</span> <span
                    class="text-gray-900 dark:text-white">{{ number_format($salesOrder->tax ?? 0, 2) }}</span></div>
            <div class="font-extrabold text-2xl text-blue-900 dark:text-blue-200">
                Grand Total: ₱{{ number_format($salesOrder->grand_total ?? 0, 2) }}
            </div>
        </div>
    </div>
</div>
