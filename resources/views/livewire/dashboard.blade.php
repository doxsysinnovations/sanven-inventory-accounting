<?php

use App\Models\Product;
use App\Models\Invoice;
use App\Models\Stock;
use Livewire\Volt\Component;
use Illuminate\Support\Carbon;

new class extends Component {

    public $totalProducts = 0;
    public $totalInvoices = 0;
    public $expiredStocks = [];
    public $overdueInvoices = [];
    public $invoiceStatusCounts = [];
    public $expiringSoonStocks = [];
    public $chartData = [];
    public $totalExpiredProducts = 0;
    public $currentMonth;
    public $topCustomers = [];
    public $topSuppliers = [];
    public $lowStockItems = [];
    public $salesToday = 0; //For Income Sales Today card

    public function mount()
    {
        $today = Carbon::today();
        $this->currentMonth = $today->format('F');
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $this->totalProducts = Product::count();
        $this->totalInvoices = Invoice::count();
        $this->expiredStocks = Stock::whereNotNull('expiration_date')
            ->where('expiration_date', '<=', [$today])
            ->orderBy('expiration_date', 'desc')
            ->with('product')
            ->get();

        $this->totalExpiredProducts = $this->expiredStocks->count();

        $this->overdueInvoices = Invoice::where('due_date', '<', [$today])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with('customer')
            ->orderBy('due_date', 'asc')
            ->get();

        //Invoice status counts
        $paid = Invoice::where('status', 'paid')->count();
        $pending = Invoice::where('status', 'pending')->count();
        $overdue = Invoice::where('status', 'overdue')->count();
        $cancelled = Invoice::where('status', 'cancelled')->count();
        $total = $paid + $pending + $overdue + $cancelled;

        $this->invoiceStatusCounts = [
            'paid' => [
                'count' => $paid,
                'percent' => $total ? round(($paid / $total) * 100, 1) : 0,
            ],
            'pending' => [
                'count' => $pending,
                'percent' => $total ? round(($pending / $total) * 100, 1) : 0,
            ],
            'overdue' => [
                'count' => $overdue,
                'percent' => $total ? round(($overdue / $total) * 100, 1) : 0,
            ],
            'cancelled' => [
                'count' => $cancelled,
                'percent' => $total ? round(($cancelled / $total) * 100, 1) : 0,
            ],
        ];


        //Expiring products soon
        $stocks = Stock::whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$today, $today->copy()->addDays(7)])
            ->with('product')
            ->get();

        $data = [
            ['score', 'amount', 'product']
        ];

        foreach ($stocks as $stock) {
            $daysToExpire = $today->diffInDays(Carbon::parse($stock->expiration_date), false);
            if ($daysToExpire <= 0) {
                $score = 0;
            } else {
                $score = $daysToExpire;
            }


            $data[] = [
                $score,
                $stock->quantity,
                ($stock->product->product_code ?? '-') . ' - ' . ($stock->product->name ?? $stock->product_name),
            ];
        }

        $this->chartData = $data;

        //Top Customers by Total Spent
        $this->topCustomers = Invoice::whereBetween('issued_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('customer_id, SUM(grand_total) as total_spent')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->take(5)
            ->get();

        $this->topSuppliers = Stock::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotNull('supplier_id')
            ->selectRaw('supplier_id, COUNT(*) as delivery_count')
            ->groupBy('supplier_id')
            ->orderByDesc('delivery_count')
            ->with('supplier')
            ->take(5)
            ->get();

        //Low Stock Items (total quantity per product <= 50)
        $this->lowStockItems = Stock::selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->filter(function ($item) {
                // Only include if product exists and total_quantity <= product's low_stock_value
                return $item->product && $item->total_quantity <= ($item->product->low_stock_value ?? 0);
            })
            ->values();
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <h1 class="font-bold sm:text-sm md:text-lg lg:text-xl">
        Dashboard
    </h1>
    <!-- General Statistics -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <x-stat-card :value="$salesToday" label="Income Sales Today" cardColor="bg-white" iconColor="text-white
            text-white" iconBackgroundColor="bg-[var(--color-accent)]">
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        </x-stat-card>
        <x-stat-card :value="$totalProducts" label="Total Products" cardColor="bg-white" iconColor="text-white
            text-white" iconBackgroundColor="bg-[var(--color-accent-2)]">
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        </x-stat-card>
        <x-stat-card :value="$totalInvoices" label="Total Invoices" cardColor="bg-white" iconColor="text-white
            text-white" iconBackgroundColor="bg-[var(--color-accent)]">
            <svg class=" w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
        </x-stat-card>
        <x-stat-card :value="$totalExpiredProducts" label="Expired Products" cardColor="bg-white" iconColor="text-white
            text-white" iconBackgroundColor="bg-[var(--color-accent-2)]">
            <svg class=" w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
        </x-stat-card>
    </div>

    <div>
        <h1 class="font-bold sm:text-sm md:text-lg lg:text-xl">
            Reports
        </h1>
    </div>

    <!-- Reports -->
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="grid auto-rows-min gap-6 md:grid-cols-2">
            <x-reports-data-table title="Overdue Invoices" :headers="['Invoice #', 'Customer', 'Due', 'Low']"
                headerBackgroundColor="bg-white" emptyMessage="There are currently no overdue invoices."
                :rows="$overdueInvoices->map(fn($invoice) => [
        $invoice->invoice_number,
        $invoice->customer->name ?? 'Unknown Customer',
        \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y'),
    ])->toArray()"
                :rowColors="$overdueInvoices->map(fn($invoice) => [
        '',
        'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold', // Qty
        'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold' // Low
    ])->toArray()" />

            <!-- Items with less than 50 in stock -->
            <x-reports-data-table title="Low Stock Items" description="Items below low stock value" :headers="['Code', 'Name', 'Qty', 'Low']" headerBackgroundColor="bg-white" :rows="$lowStockItems->map(fn($item) => [
        $item->product->product_code ?? '-',
        $item->product->name ?? 'Unknown Product',
        $item->total_quantity,
        $item->product->low_stock_value ?? '-',
    ])->toArray()" :rowColors="$lowStockItems->map(fn($item) => [
        '',
        '',
        'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold', // Qty
        'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold' // Low
    ])->toArray()" />

            <!-- Expired Products -->
            <x-reports-data-table title="Expired Products" description="Products past their expiration date"
                :headers="['Code', 'Name', 'Qty', 'Expiration']" headerBackgroundColor="bg-white"
                :rows="$expiredStocks->map(fn($stock) => [
        $stock->product->product_code ?? '-',
        $stock->product->name ?? 'Unknown Product',
        $stock->quantity . ' ' . ($stock->product?->unit?->name ?? ''),
        \Carbon\Carbon::parse($stock->expiration_date)->format('M d, Y'),
    ])->toArray()"
                :rowColors="$expiredStocks->map(fn($stock) => [
        '', // No badge for Code
        '', // No badge for Name
        '', // Qty
        'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold', // Expiration
    ])->toArray()" />

            <!-- Returned/Rejected Products -->
            <x-reports-data-table title="Returned Products" :headers="['Product', 'Returned']" :rows="[
        ['Surgical Gloves (Box of 100)', '10 boxes returned'],
        ['Face Masks (Box of 50)', '5 boxes returned'],
        ['Digital Thermometers', '3 units rejected'],
    ]" : rowColors="[
                            ['', 'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold'],
                            ['', 'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold'],
                            ['', 'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold'],
                        ]" headerBackgroundColor="bg-white" />
        </div>

        <!-- Aging Reports Table -->
        <div
            class="bg-white shadow-lg rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Aging Reports</h3>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Agent</th>
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Invoice #</th>
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Total Amount
                        </th>
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">John Doe</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">#1001</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">₱75,000</td>
                        <td class="py-2 px-4 text-sm text-red-500 font-semibold">Overdue</td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">Jane Smith</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">#1002</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">₱83,200</td>
                        <td class="py-2 px-4 text-sm text-yellow-500 font-semibold">Pending</td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">Michael Brown</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">#1003</td>
                        <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">₱197,800</td>
                        <td class="py-2 px-4 text-sm text-green-500 font-semibold">Paid</td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-4 text-right">
                <button class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                    See All
                </button>
            </div>
        </div>

        <!-- Top Customers / Top Suppliers -->
        <div class="grid gap-6 md:grid-cols-2 grid-rows-2">

            <!-- Top Suppliers by Delivery -->
            <div class="row-start-1 col-start-1">
                <div
                    class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Top Suppliers of
                        {{ $currentMonth }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Based on Total Deliveries</p>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">#</th>
                                <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Supplier
                                    Name
                                </th>
                                <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Deliveries
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSuppliers as $i => $supplier)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">{{ $i + 1 }}</td>
                                    <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $supplier->supplier->name ?? 'Unknown Supplier' }}
                                    </td>
                                    <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $supplier->delivery_count }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-2 px-4 text-sm text-gray-500 dark:text-gray-400">No suppliers
                                        found for the month of {{ $currentMonth }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4 text-right">
                        <a href="{{ route('suppliers') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            All Suppliers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Customers by Total Spent -->
            <div class="row-start-2 col-start-1">
                <div
                    class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Top Customers of
                        {{ $currentMonth }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Based on Total Spent</p>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">#</th>
                                <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Customer
                                    Name
                                </th>
                                <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Grand Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $i => $customer)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">{{ $i + 1 }}</td>
                                    <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $customer->customer->name ?? 'Unknown Customer' }}
                                    </td>
                                    <td class="py-2 px-4 text-sm text-gray-700 dark:text-gray-300">
                                        ₱{{ number_format($customer->total_spent, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-2 px-4 text-sm text-gray-500 dark:text-gray-400">No customers
                                        found for the month of {{ $currentMonth }} .
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4 text-right">
                        <a href="{{ route('customers') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            All Customers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="row-span-2 col-start-2">
                <div
                    class="relative h-full overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Monthly Sales</h3>
                    <div id="monthlySales" class="w-full h-full"></div>
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex h-full w-full flex-1 flex-col gap-4 col-span-1 md:col-span-2">

            <!-- Invoice Status -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Invoice Status</h3>
                <div class="flex flex-col items-center">
                    <div id="invoiceStatusChart" style="width: 100%; height: 300px;"></div>
                    <div class="mt-4 flex flex-row gap-5 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">Paid:
                                {{ $invoiceStatusCounts['paid']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-yellow-300"></span>
                            <span class="text-gray-700 dark:text-gray-300">Pending:
                                {{ $invoiceStatusCounts['pending']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-orange-400"></span>
                            <span class="text-gray-700 dark:text-gray-300">Overdue:
                                {{ $invoiceStatusCounts['overdue']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">Cancelled:
                                {{ $invoiceStatusCounts['cancelled']['percent'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Expiring Products Soon -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Expiring Products Soon</h3>
                @if(count($chartData) <= 1)
                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                        No products expiring within 7 days.
                    </div>
                @else
                    <div id="expiringChart" style="width: 100%; height: 320px;"></div>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>

    //Monthly Sales Line chart
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        var chartDom = document.getElementById('monthlySales');
        var myChart = echarts.init(chartDom);
        var option;

        option = {
            xAxis: {
                type: 'category',
                data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            },
            yAxis: {
                type: 'value'
            },
            series: [
                {
                    data: [150, 230, 224, 218, 135, 147, 260],
                    type: 'line'
                }
            ]
        };

        option && myChart.setOption(option);
    });

    //Pie chart for Invoice Status
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        var chartDom = document.getElementById('invoiceStatusChart');
        var myChart = echarts.init(chartDom);
        var option;

        var paid = {{ $invoiceStatusCounts['paid']['count'] }};
        var pending = {{ $invoiceStatusCounts['pending']['count'] }};
        var overdue = {{ $invoiceStatusCounts['overdue']['count'] }};
        var cancelled = {{ $invoiceStatusCounts['cancelled']['count'] }};
        var total = paid + pending + overdue + cancelled;

        var data = [];
        if (total === 0) {
            data = [{ value: 0, name: 'No Invoice', itemStyle: { color: '#d1d5db' } }];
        } else {
            data = [
                { value: paid, name: 'Paid', itemStyle: { color: '#22c55e' } },
                { value: pending, name: 'Pending', itemStyle: { color: '#fde047' } },
                { value: overdue, name: 'Overdue', itemStyle: { color: '#fb923c' } },
                { value: cancelled, name: 'Cancelled', itemStyle: { color: '#ef4444' } }
            ];
        }

        option = {
            tooltip: { trigger: 'item' },
            legend: {
                left: 'center',
                textStyle: {
                    color: isDark ? '#fff' : '#222'
                }
            },
            series: [
                {
                    name: 'Invoice Status',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 5,
                    },
                    label: { show: false, position: 'center' },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: { show: false },
                    data: data
                }
            ]
        };

        option && myChart.setOption(option);
    });

    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const chart = echarts.init(document.getElementById('expiringChart'));
        const option = {
            dataset: {
                source: @js($chartData)
            },
            grid: {
                containLabel: true,
                left: '3%',
            },
            xAxis: { name: 'Qty' },
            yAxis: { type: 'category' },
            visualMap: {
                orient: 'horizontal',
                left: 'center',
                min: 0,
                max: 7,
                text: ['Expiring in 7 days', 'Expiring Soon'],
                dimension: 0,
                inRange: {
                    color: ['#FF4C4C', '#FFD700', '#65B581']
                },
                textStyle: {
                    color: isDark ? '#fff' : '#222'
                }
            },
            series: [
                {
                    type: 'bar',
                    encode: {
                        x: 'amount',
                        y: 'product'
                    }
                }
            ]
        };

        chart.setOption(option);
    });</script>