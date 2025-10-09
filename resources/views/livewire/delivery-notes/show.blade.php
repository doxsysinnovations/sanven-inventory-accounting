<?php

use Livewire\Volt\Component;
use App\Models\DeliveryNote;

new class extends Component {
    public DeliveryNote $deliveryNote;
    public bool $editMode = false;
    public array $items = [];
    public $delivery_date, $status, $remarks;

    public function mount(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['salesOrder.customer', 'salesOrder.agent', 'items.product', 'items.batches']);
        $this->deliveryNote = $deliveryNote;
        $this->delivery_date = is_string($deliveryNote->delivery_date) ? $deliveryNote->delivery_date : $deliveryNote->delivery_date?->format('Y-m-d');
        $this->status = $deliveryNote->status;
        $this->remarks = $deliveryNote->remarks;

        $this->items = $deliveryNote->items
            ->map(function ($item) {
                $totalDelivered = $item->batches->sum('quantity_delivered');
                $totalBackorder = $item->batches->sum('backorder_quantity');

                return [
                    'id' => $item->id,
                    'product_name' => $item->product?->name ?? 'N/A',
                    'strength' => $item->product?->strength ?? 'N/A',
                    'unit' => $item->product?->unit?->name ?? 'N/A',
                    'type' => $item->product?->type?->name ?? 'N/A',
                    'ordered_qty' => $item->ordered_qty,
                    'delivered_qty' => $item->delivered_qty,
                    'backorder_qty' => $item->backorder_qty,
                    'batches' => $item->batches
                        ->map(function ($batch) {
                            return [
                                'id' => $batch->id,
                                'batch_number' => $batch->batch_number,
                                'quantity_delivered' => $batch->quantity_delivered,
                                'backorder_quantity' => $batch->backorder_quantity,
                                'expiry_date' => $batch->expiry_date,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->toArray();
    }

    public function enableEdit()
    {
        if ($this->deliveryNote->status !== 'delivered') {
            $this->editMode = true;
        }
    }

    public function saveAll()
    {
        $this->validate([
            'delivery_date' => 'required|date',
            'status' => 'required|string|in:pending,partially_delivered,delivered,completed,cancelled',
            'remarks' => 'nullable|string|max:1000',
            'items.*.delivered_qty' => 'required|integer|min:0',
            'items.*.backorder_qty' => 'required|integer|min:0',
        ]);

        $this->deliveryNote->delivery_date = $this->delivery_date;
        $this->deliveryNote->status = $this->status;
        $this->deliveryNote->remarks = $this->remarks;
        $this->deliveryNote->save();

        foreach ($this->items as $i => $item) {
            $deliveryNoteItem = $this->deliveryNote->items[$i];
            $deliveryNoteItem->delivered_qty = $item['delivered_qty'];
            $deliveryNoteItem->backorder_qty = $item['backorder_qty'];
            $deliveryNoteItem->save();
        }

        $this->editMode = false;
        session()->flash('success', 'Delivery note updated!');
    }

    public function removeItem($index)
    {
        $deliveryNoteItem = $this->deliveryNote->items[$index];
        $deliveryNoteItem->delete();
        $this->deliveryNote->refresh();
        $this->items = $this->deliveryNote->items
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product?->name ?? 'N/A',
                    'strength' => $item->product?->strength ?? 'N/A',
                    'unit' => $item->product?->unit?->name ?? 'N/A',
                    'type' => $item->product?->type?->name ?? 'N/A',
                    'ordered_qty' => $item->ordered_qty,
                    'delivered_qty' => $item->delivered_qty,
                    'backorder_qty' => $item->backorder_qty,
                    'batches' => $item->batches
                        ->map(function ($batch) {
                            return [
                                'id' => $batch->id,
                                'batch_number' => $batch->batch_number,
                                'quantity_delivered' => $batch->quantity_delivered,
                                'backorder_quantity' => $batch->backorder_quantity,
                                'expiry_date' => $batch->expiry_date,
                            ];
                        })
                        ->toArray(),
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
                Delivery Note Details
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                Review delivery information, customer details, and product breakdown for this delivery note.
            </p>
        </div>
        <div class="flex gap-2 mt-3 sm:mt-0">
            <flux:button href="{{ route('delivery-notes') }}" icon="arrow-left" variant="outline" size="sm">
                Back
            </flux:button>
            @if ($deliveryNote->status !== 'delivered' && !$editMode)
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
            <flux:button href="{{ route('delivery-notes.stream-pdf', $deliveryNote->id) }}" icon="printer"
                variant="primary" size="sm">
                Print Delivery Note
            </flux:button>
            <flux:dropdown>
                <flux:button icon="ellipsis-vertical" variant="outline" size="sm" />
                <flux:menu>
                    <flux:menu.item wire:click="cancelDeliveryNote({{ $deliveryNote->id }})" icon="x-circle"
                        class="text-red-600">
                        Cancel Delivery Note
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
                    class="text-gray-900 dark:text-white">{{ $deliveryNote->salesOrder->customer?->name ?? 'N/A' }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> <span
                    class="text-gray-900 dark:text-white">{{ $deliveryNote->salesOrder->customer?->email ?? 'N/A' }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Phone:</span> <span
                    class="text-gray-900 dark:text-white">{{ $deliveryNote->salesOrder->customer?->phone ?? 'N/A' }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Address:</span> <span
                    class="text-gray-900 dark:text-white">{{ $deliveryNote->salesOrder->customer?->address ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 space-y-10 rounded-lg shadow-lg">

        {{-- Delivery Note Info --}}
        <form wire:submit.prevent="saveAll">
            <div>
                <h4 class="font-semibold text-xl text-blue-800 dark:text-blue-200 mb-4">Delivery Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-base">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">DN Number:</span>
                        <span
                            class="text-gray-900 dark:text-white">{{ $deliveryNote->delivery_note_number ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">SO Number:</span>
                        <span
                            class="text-gray-900 dark:text-white">{{ $deliveryNote->salesOrder->order_number ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Delivery Date:</span>
                        @if ($editMode)
                            <input type="date" wire:model="delivery_date" class="form-input w-full" />
                        @else
                            <span
                                class="text-gray-900 dark:text-white">{{ $deliveryNote->delivery_date ?? 'N/A' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                        @if ($editMode)
                            <select wire:model.defer="status" class="form-select w-full">
                                <option value="pending">Pending</option>
                                <option value="partially_delivered">Partially Delivered</option>
                                <option value="delivered">Delivered</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        @else
                            <span
                                class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                @if ($deliveryNote->status === 'delivered' || $deliveryNote->status === 'completed') bg-green-100 text-green-800
                                @elseif($deliveryNote->status === 'partially_delivered') bg-yellow-100 text-yellow-800
                                @elseif($deliveryNote->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $deliveryNote->status ?? 'N/A')) }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Agent:</span>
                        <span
                            class="text-gray-900 dark:text-white">{{ $deliveryNote->salesOrder->agent?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Remarks:</span>
                        @if ($editMode)
                            <textarea wire:model.defer="remarks" class="form-input w-full" rows="3"></textarea>
                        @else
                            <span class="text-gray-900 dark:text-white">{{ $deliveryNote->remarks ?? 'N/A' }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="mt-10">
                <h4 class="font-semibold text-xl text-blue-800 dark:text-blue-200 mb-4">Delivery Items</h4>
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
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">Ordered
                                    Qty</th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">
                                    Delivered Qty</th>
                                <th class="px-4 py-3 text-right font-semibold text-blue-900 dark:text-blue-200">
                                    Backorder Qty</th>
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
                                    <td class="px-4 py-2 text-right">{{ $item['ordered_qty'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if ($editMode)
                                            <input type="number" min="0" max="{{ $item['ordered_qty'] }}"
                                                wire:model.defer="items.{{ $i }}.delivered_qty"
                                                class="form-input w-20" />
                                        @else
                                            {{ $item['delivered_qty'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        @if ($editMode)
                                            <input type="number" min="0"
                                                wire:model.defer="items.{{ $i }}.backorder_qty"
                                                class="form-input w-20" />
                                        @else
                                            {{ $item['backorder_qty'] }}
                                        @endif
                                    </td>
                                    @if ($editMode)
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" wire:click="removeItem({{ $i }})"
                                                class="text-red-600 hover:underline">Remove</button>
                                        </td>
                                    @endif
                                </tr>

                                {{-- Batch Details --}}
                                @if (!empty($item['batches']))
                                    @foreach ($item['batches'] as $batch)
                                        <tr class="border-t dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                                            <td colspan="4"
                                                class="px-4 py-1 text-sm text-gray-600 dark:text-gray-400">
                                                Batch: {{ $batch['batch_number'] }}
                                                @if ($batch['expiry_date'])
                                                    (Exp:
                                                    {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('M Y') }})
                                                @endif
                                            </td>
                                            <td class="px-4 py-1 text-right text-sm text-gray-600 dark:text-gray-400">
                                                -
                                            </td>
                                            <td class="px-4 py-1 text-right text-sm text-gray-600 dark:text-gray-400">
                                                {{ $batch['quantity_delivered'] }} delivered
                                            </td>
                                            <td class="px-4 py-1 text-right text-sm text-gray-600 dark:text-gray-400">
                                                {{ $batch['backorder_quantity'] }} backorder
                                            </td>
                                            @if ($editMode)
                                                <td class="px-4 py-1"></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
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

        {{-- Delivery Summary --}}
        <div class="text-right space-y-1 mt-8">
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Total Items Ordered:</span> <span
                    class="text-gray-900 dark:text-white">{{ number_format(array_sum(array_column($items, 'ordered_qty')) ?? 0, 0) }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Total Items Delivered:</span> <span
                    class="text-gray-900 dark:text-white">{{ number_format(array_sum(array_column($items, 'delivered_qty')) ?? 0, 0) }}</span>
            </div>
            <div><span class="font-medium text-gray-700 dark:text-gray-300">Total Backorder Items:</span> <span
                    class="text-gray-900 dark:text-white">{{ number_format(array_sum(array_column($items, 'backorder_qty')) ?? 0, 0) }}</span>
            </div>
            <div class="font-extrabold text-2xl text-blue-900 dark:text-blue-200 mt-4">
                Delivery Completion:
                {{ number_format((array_sum(array_column($items, 'delivered_qty')) / max(array_sum(array_column($items, 'ordered_qty')), 1)) * 100, 1) }}%
            </div>
        </div>
    </div>
</div>
