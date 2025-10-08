<?php

use Livewire\Volt\Component;
use App\Models\SalesOrder;
use App\Models\DeliveryNote;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $deliveryNoteNumber;
    public $salesOrderId;
    public $salesOrder;
    public $items = [];
    public $customer;
    public $remarks;

    public function mount()
    {
        $this->salesOrderId = request()->get('sales_order_id');
        // 🚨 If no SO ID passed, redirect back to Sales Order list
        if (!$this->salesOrderId) {
            session()->flash('error', 'Please select a Sales Order first.');
            return redirect()->route('sales-orders');
        }
        $this->salesOrder = SalesOrder::with(['customer', 'items.product'])->findOrFail($this->salesOrderId);
        $this->customer = $this->salesOrder->customer;

        $lastDn = DeliveryNote::latest('id')->first();
        $nextId = $lastDn ? $lastDn->id + 1 : 1;
        $this->deliveryNoteNumber = 'DN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        foreach ($this->salesOrder->items as $item) {
            $stocks = Stock::where('product_id', $item->product->id)
                ->orderBy('expiration_date') // FIFO
                ->get();

            $totalStock = $stocks->sum('quantity');
            $allocated = min($item->quantity, $totalStock);
            $backorder = max(0, $item->quantity - $totalStock);
            $this->items[] = [
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'strength' => $item->product->strength ?? '—',
                'unit' => $item->product->unit->name ?? ($item->product->unit_name ?? '—'),
                'type' => $item->product->type->name ?? ($item->product->type_name ?? '—'),
                'ordered_qty' => $item->quantity,
                'delivered_qty' => $allocated,
                'backorder' => $backorder,
                'total_stock' => $totalStock,
                'batches' => $stocks
                    ->map(
                        fn($s) => [
                            'stock_id' => $s->id,
                            'batch_number' => $s->batch_number ?? '—',
                            'expiration_date' => $s->expiration_date ?? null,
                            'available_qty' => $s->quantity,
                            'allocated_qty' => 0,
                        ],
                    )
                    ->toArray(),
            ];

            // Automatically allocate batches based on delivered_qty
            $remaining = $allocated;
            foreach ($this->items[count($this->items) - 1]['batches'] as &$batch) {
                if ($remaining <= 0) {
                    break;
                }

                $allocate = min($batch['available_qty'], $remaining);
                $batch['allocated_qty'] = $allocate;
                $remaining -= $allocate;
            }
            unset($batch);
        }
    }

    public function updatedItems($value, $key)
    {
        [$index, $field] = explode('.', $key);

        if ($field === 'delivered_qty') {
            $ordered = $this->items[$index]['ordered_qty'];
            $totalStock = $this->items[$index]['total_stock'];
            $delivered = min($value, $totalStock); // cap at stock

            // reset batches
            foreach ($this->items[$index]['batches'] as &$batch) {
                $batch['allocated_qty'] = 0;
            }

            $remaining = $delivered;
            foreach ($this->items[$index]['batches'] as &$batch) {
                if ($remaining <= 0) {
                    break;
                }

                $allocate = min($batch['available_qty'], $remaining);
                $batch['allocated_qty'] = $allocate;
                $remaining -= $allocate;
            }

            $this->items[$index]['backorder'] = max(0, $ordered - $delivered);
            $this->items[$index]['delivered_qty'] = $delivered;
        }
    }

    public function save()
    {
        // 1. Check if all items are already fully delivered for this SO
        $existingDelivered = DeliveryNote::where('sales_order_id', $this->salesOrder->id)
            ->with('items')
            ->get()
            ->flatMap->items
            ->groupBy('product_id')
            ->map(fn($items) => $items->sum('delivered_qty'));
        $alreadyDelivered = true;
        foreach ($this->items as $item) {

            $delivered = $existingDelivered[$item['product_id']] ?? 0;
// dd($item['ordered_qty']);

            if ($delivered < $item['ordered_qty']) {
                $alreadyDelivered = false;
                break;
            }
        }
        if ($alreadyDelivered) {
            session()->flash('error', 'All items for this Sales Order have already been delivered.');
            return;
        }

        // 2. Prevent over-delivery (do not allow delivered_qty to exceed ordered_qty - already delivered)
        foreach ($this->items as $i => $item) {
            $delivered = $existingDelivered[$item['product_id']] ?? 0;
            $maxDeliverable = $item['ordered_qty'] - $delivered;
            if ($item['delivered_qty'] > $maxDeliverable) {
                $this->items[$i]['delivered_qty'] = $maxDeliverable;
                $this->items[$i]['backorder'] = max(0, $item['ordered_qty'] - ($delivered + $maxDeliverable));
            }
        }

        DB::beginTransaction();

        try {
            $dn = DeliveryNote::create([
                'delivery_note_number' => $this->deliveryNoteNumber,
                'sales_order_id' => $this->salesOrder->id,
                'remarks' => $this->remarks,
            ]);

            foreach ($this->items as $item) {
                // Only create DN item if delivered_qty > 0
                if ($item['delivered_qty'] <= 0) continue;

                $dnItem = $dn->items()->create([
                    'product_id' => $item['product_id'],
                    'ordered_qty' => $item['ordered_qty'],
                    'delivered_qty' => $item['delivered_qty'],
                    'backorder_qty' => $item['backorder'],
                ]);
                foreach ($item['batches'] as $batch) {
                    if ($batch['allocated_qty'] > 0) {
                        $dnItem->batches()->create([
                            'stock_id' => $batch['stock_id'],
                            'allocated_qty' => $batch['allocated_qty'],
                        ]);

                        $updated = Stock::where('id', $batch['stock_id'])->decrement('quantity', $batch['allocated_qty']);

                        if ($updated === 0) {
                            throw new \Exception("Failed to decrement stock ID {$batch['stock_id']}");
                        }
                    }
                }
            }

            DB::commit();
            session()->flash('success', 'Delivery Note Created!');
            return redirect()->route('delivery-notes.show', $dn->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save Delivery Note: ' . $e->getMessage());
            session()->flash('error', 'Failed to create Delivery Note. ' . $e->getMessage());
        }
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
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <a href="{{ route('sales-orders') }}"
                            class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 md:ml-2">
                            Sales Orders
                        </a>
                    </div>
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


    <!-- Delivery Notes List Button -->
    <div class="mb-4 flex justify-end">
        <a href="{{ route('delivery-notes') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10" />
            </svg>
            Delivery Notes List
        </a>
    </div>
    <div class="p-8 bg-white rounded-xl shadow space-y-6">
        <!-- Header -->
        <div class="flex justify-between border-b pb-4">
            <div>
                <h1 class="text-2xl font-bold">Delivery Note</h1>
                <p class="text-sm text-gray-600">Allocation of stocks (FIFO)</p>
            </div>
            <div class="text-right">
                <p><span class="font-semibold">DN #:</span> {{ $deliveryNoteNumber }}</p>
                <p><span class="font-semibold">Reference SO #:</span> {{ $salesOrder->order_number }}</p>
                <p><span class="font-semibold">SO Date:</span>
                    {{ $salesOrder->order_date ? \Carbon\Carbon::parse($salesOrder->order_date)->format('M d, Y') : 'N/A' }}
                </p>
                <span
                    class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                    Pending Allocation
                </span>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="grid grid-cols-2 gap-6 text-sm">
            <div class="bg-gray-50 p-4 rounded">
                <h2 class="font-semibold mb-2">Customer</h2>
                <p>{{ $customer->name }}</p>
                <p>{{ $customer->address ?? 'No address' }}</p>
                <p>{{ $customer->contact ?? 'No contact' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <h2 class="font-semibold mb-2">Delivery Details</h2>
                <p><span class="font-semibold">Planned Date:</span> {{ now()->format('M d, Y') }}</p>
                <p><span class="font-semibold">Status:</span> Draft</p>
                <p><span class="font-semibold">Prepared By:</span> {{ auth()->user()->name ?? 'System' }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <h2 class="font-semibold mb-2">Products</h2>
            <table class="w-full border text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Product</th>
                        <th class="border p-2 text-center">Strength</th>
                        <th class="border p-2 text-center">Unit</th>
                        <th class="border p-2 text-center">Type</th>
                        <th class="border p-2 text-center">Ordered Qty</th>
                        <th class="border p-2 text-center">Inventory Stock</th>
                        <th class="border p-2 text-center">Allocated Qty</th>
                        <th class="border p-2 text-center">Backorder Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr class="bg-white">
                            <td class="border p-2">{{ $item['product_name'] }}</td>
                            <td class="border p-2 text-center">{{ $item['strength'] }}</td>
                            <td class="border p-2 text-center">{{ $item['unit'] }}</td>
                            <td class="border p-2 text-center">{{ $item['type'] }}</td>
                            <td class="border p-2 text-center">{{ $item['ordered_qty'] }}</td>
                            <td class="border p-2 text-center">{{ $item['total_stock'] }}</td>
                            <td class="border p-2 text-center">
                                <input type="number" wire:model="items.{{ $index }}.delivered_qty"
                                    class="w-20 border rounded p-1 text-center" />
                            </td>
                            <td
                                class="border p-2 text-center {{ $item['backorder'] > 0 ? 'text-red-600 font-semibold' : '' }}">
                                {{ $item['backorder'] }}
                            </td>
                        </tr>
                        <!-- Batch allocations -->
                        <tr class="bg-gray-50 text-xs">
                            <td colspan="8" class="p-2">
                                <div class="space-y-1">
                                    @foreach ($item['batches'] as $batch)
                                        <div class="flex justify-between">
                                            <span>
                                                Batch: {{ $batch['batch_number'] }}
                                                @if ($batch['expiration_date'])
                                                    (Exp:
                                                    {{ \Carbon\Carbon::parse($batch['expiration_date'])->format('M d, Y') }})
                                                @endif
                                            </span>
                                            <span>Available: {{ $batch['available_qty'] }} |
                                                Allocated: {{ $batch['allocated_qty'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Remarks -->
        <div>
            <label class="block font-semibold mb-1">Remarks</label>
            <textarea wire:model="remarks" rows="3" class="w-full border rounded p-2"></textarea>
        </div>

        <!-- Save -->
        <div class="flex justify-end">
            <button wire:click="save"
                class="bg-accent text-white px-6 py-2 rounded-lg cursor-pointer hover:bg-cyan-700">
                Create Delivery Note
            </button>
        </div>
    </div>
</div>
