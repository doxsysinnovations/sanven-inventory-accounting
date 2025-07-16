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
    public $agingReports = [];
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

        $this->agingReports=Invoice::get();
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
            text-white" iconBackgroundColor="bg-[var(--color-accent)]">
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
            <x-reports-data-table 
                title="Overdue Invoices" 
                :headers="['Invoice #', 'Customer', 'Due']"
                emptyMessage="There are currently no overdue invoices." 
                :rows="$overdueInvoices->map(fn($invoice) => [
                    $invoice->invoice_number,
                    $invoice->customer->name ?? 'Unknown Customer',
                    \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y'),
                ])->toArray()"
                :rowColors="$overdueInvoices->map(fn($invoice) => [
                    '',
                    '',
                    'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold', // Qty
                ])->toArray()" />

            <!-- Items with less than 50 in stock -->
            <x-reports-data-table 
                title="Low Stock Items" 
                description="Items below low stock value" 
                :headers="['Code', 'Name', 'Qty', 'Low']" 
                :rows="$lowStockItems->map(fn($item) => [
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
            <x-reports-data-table title="Expired Products" 
                description="Products past their expiration date"
                :headers="['Code', 'Name', 'Qty', 'Expiration']" 
                :rows="$expiredStocks->map(fn($stock) => [
                    $stock->product->product_code ?? '-',
                    $stock->product->name ?? 'Unknown Product',
                    $stock->quantity . ' ' . ($stock->product?->unit?->name ?? ''),
                    \Carbon\Carbon::parse($stock->expiration_date)->format('M d, Y'),
                ])->toArray()" :rowColors="$expiredStocks->map(fn($stock) => [
                    '', // No badge for Code
                    '', // No badge for Name
                    '', // Qty
                    'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold', // Expiration
                ])->toArray()" />

            <!-- Returned/Rejected Products -->
            <x-reports-data-table 
                title="Returned Products" 
                :headers="['Product', 'Returned']" 
                :rows="[
                    ['Surgical Gloves (Box of 100)', '10 boxes returned'],
                    ['Face Masks (Box of 50)', '5 boxes returned'],
                    ['Digital Thermometers', '3 units rejected'],
                ]" :rowColors="[
                    [null, 'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold'],
                    [null, 'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold'],
                    [null, 'bg-[#FFEAE8] text-[color:var(--color-accent-2)] px-3 py-1 font-semibold'],
                ]" />
        </div>

        <!-- Aging Reports Table -->
        <x-reports-data-table-with-status 
            title="Aging Reports" 
            :headers="['Agent', 'Inovice #', 'Total Amount', 'Status']" 
            :rows="$agingReports->map(fn($report)=>[
                $report->agent_id ?? 'null',
                $report->invoice_number,
                '₱' . number_format($report->total_amount, 2),
                $report->status,
            ])"
            route="agingreports"
        />

        <!-- Top Customers / Top Suppliers -->
        <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
            <!-- Top Suppliers by Delivery -->
            <x-reports-data-table-with-button
                styleAttributes="md:row-start-1 md:col-start-1"
                title="Top Suppliers of {{ $currentMonth }}" 
                description="Based on Total Deliveries"
                :headers="['#', 'Supplier Name', 'Deliveries']" 
                :rows="$topSuppliers->map(fn($supplier, $i)=>[
                    $supplier->supplier->name ?? 'Unknown Supplier',
                    $supplier->delivery_count,
                    $i + 1
                ])"
                emptyMessage="No suppliers found for the month of {{ $currentMonth }}."
                buttonLabel="All Suppliers"
                route="suppliers"
            />

            <!-- Top Customers by Total Spent -->
            <x-reports-data-table-with-button
                styleAttributes="md:row-start-2 md:col-start-1"
                description="Based on Total Spent"
                title="Top Customers of {{ $currentMonth }}" 
                :headers="['#', 'Customer Name', 'Grand Total']" 
                :rows="$topCustomers->map(fn($customer, $i)=>[
                    $i + 1,
                    $customer->customer->name ?? 'Unknown Customer',
                    '₱' . number_format($customer->total_spent, 2)
                ])"
                emptyMessage="No suppliers found for the month of {{ $currentMonth }}"
                buttonLabel="All Customers"
                route="customers"
            />

            <!-- Monthly Sales -->
            <div class="md:row-span-2 md:col-start-2">
                <div
                    class="relative h-full overflow-hidden rounded-md shadow border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800">
                    
                    <div class="p-4 bg-gray-50">
                         <h3 class="text-lg font-semibold text-(--color-accent) dark:text-gray-100">Monthly Sales</h3>
                    </div>

                    <div id="monthlySales" class="w-full h-full"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Invoice Status -->
        <div class="relative overflow-hidden rounded-md shadow border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800">
                <div class="mb-4 p-4 bg-gray-50">
                        <h3 class="text-lg font-semibold text-(--color-accent) dark:text-gray-100">Invoice Status</h3>
                </div>
                <div class="flex flex-col items-center pb-6">
                    <div id="invoiceStatusChart" class="-mb-5 p-6" style="width: 100%; height: 300px;"></div>
                    <div class="flex flex-row gap-5 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-green-400"></span>
                            <span class="text-gray-700 dark:text-gray-300">Paid:
                                {{ $invoiceStatusCounts['paid']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-(--color-yellow-400)"></span>
                            <span class="text-gray-700 dark:text-gray-300">Pending:
                                {{ $invoiceStatusCounts['pending']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-[var(--color-accent-2)]"></span>
                            <span class="text-gray-700 dark:text-gray-300">Overdue:
                                {{ $invoiceStatusCounts['overdue']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full bg-orange-400"></span>
                            <span class="text-gray-700 dark:text-gray-300">Cancelled:
                                {{ $invoiceStatusCounts['cancelled']['percent'] }}%</span>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Expiring Products Soon -->
        <div class="relative overflow-hidden rounded-md shadow border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800">
           <div class="mb-4 p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-(--color-accent) dark:text-gray-100">Expiring Products Soon</h3>
            </div>
            @if(count($chartData) <= 1)
                <div class="p-6 text-center text-gray-500 dark:text-gray-400 py-4 text-sm">
                    No products expiring within 7 days.
                </div>
            @else
                <div class="p-6" id="expiringChart" style="width: 100%; height: 320px;"></div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
    // Store chart instances globally for resize handling
    let charts = {};
    let isInitialized = false;

    // Utility function to get CSS variables
    function getCSSVariable(name) {
        return getComputedStyle(document.documentElement)
            .getPropertyValue(name)
            .trim();
    }

    // Utility function to check if element is visible and has dimensions
    function isElementReady(element) {
        return element && 
               element.offsetWidth > 0 && 
               element.offsetHeight > 0 &&
               element.offsetParent !== null;
    }

    // Wait for element to be ready with timeout
    function waitForElement(selector, callback, timeout = 5000) {
        const startTime = Date.now();
        
        function check() {
            const element = document.getElementById(selector);
            if (isElementReady(element)) {
                callback(element);
                return;
            }
            
            if (Date.now() - startTime < timeout) {
                requestAnimationFrame(check);
            } else {
                console.warn(`Element ${selector} not ready after ${timeout}ms`);
            }
        }
        
        check();
    }

    // Initialize charts with proper error handling and resize support
    function initializeCharts() {
        console.log('Initializing charts...');
        
        // Clean up existing charts first
        cleanupCharts();
        
        // Initialize each chart with element waiting
        waitForElement('monthlySales', () => initializeMonthlySales());
        waitForElement('invoiceStatusChart', () => initializeInvoiceStatus());
        waitForElement('expiringChart', () => initializeExpiringChart());
        
        isInitialized = true;
    }

    function initializeMonthlySales() {
        const chartDom = document.getElementById('monthlySales');
        if (!chartDom) return;

        try {
            const accentColor = getCSSVariable('--color-accent') || '#ef4444';
            const fontSans = getCSSVariable('--font-sans') || 'system-ui';
            
            charts.monthlySales = echarts.init(chartDom);
            
            const option = {
                tooltip: {
                    trigger: 'axis',
                    formatter: function (params) {
                        const data = params[0];
                        return `Monthly Sales: ${data.value}`;
                    }
                },
                textStyle: {
                    fontFamily: fontSans 
                },
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
                        type: 'line',
                        lineStyle: {
                            color: accentColor
                        },
                        itemStyle: {
                            color: accentColor
                        }
                    }
                ]
            };

            charts.monthlySales.setOption(option);
            console.log('Monthly Sales chart initialized');
        } catch (error) {
            console.error('Error initializing Monthly Sales chart:', error);
        }
    }

    function initializeInvoiceStatus() {
        const chartDom = document.getElementById('invoiceStatusChart');
        if (!chartDom) return;

        try {
            const accentColor = getCSSVariable('--color-accent-2') || '#ef4444';
            const yellowColor = getCSSVariable('--color-yellow-400') || '#fcc800';
            const fontSans = getCSSVariable('--font-sans') || 'system-ui';
            const isDark = document.documentElement.classList.contains('dark');
            
            charts.invoiceStatus = echarts.init(chartDom);

            // Get data from PHP variables
            const invoiceData = window.invoiceStatusData || {
                paid: {{ $invoiceStatusCounts['paid']['count'] ?? 0 }},
                pending: {{ $invoiceStatusCounts['pending']['count'] ?? 0 }},
                overdue: {{ $invoiceStatusCounts['overdue']['count'] ?? 0 }},
                cancelled: {{ $invoiceStatusCounts['cancelled']['count'] ?? 0 }}
            };

            const { paid, pending, overdue, cancelled } = invoiceData;
            const total = paid + pending + overdue + cancelled;

            const colours = {
                paid: '#4ade80',
                pending: yellowColor,
                overdue: accentColor,
                cancelled: '#fb923c'
            };

            let data = [];
            if (total === 0) {
                data = [{ value: 1, name: 'No Invoice', itemStyle: { color: '#d1d5db' } }];
            } else {
                data = [
                    {
                        value: paid,
                        name: 'Paid',
                        itemStyle: { color: colours.paid },
                        emphasis: { itemStyle: { color: colours.paid } }
                    },
                    {
                        value: pending,
                        name: 'Pending',
                        itemStyle: { color: colours.pending },
                        emphasis: { itemStyle: { color: colours.pending } }
                    },
                    {
                        value: overdue,
                        name: 'Overdue',
                        itemStyle: { color: colours.overdue },
                        emphasis: { itemStyle: { color: colours.overdue } }
                    },
                    {
                        value: cancelled,
                        name: 'Cancelled',
                        itemStyle: { color: colours.cancelled },
                        emphasis: { itemStyle: { color: colours.cancelled } }
                    }
                ];
            }

            const option = {
                tooltip: { trigger: 'item' },
                legend: {
                    left: 'center',
                    textStyle: {
                        color: isDark ? '#fff' : '#222'
                    }
                },
                textStyle: {
                    fontFamily: fontSans 
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

            charts.invoiceStatus.setOption(option);
            console.log('Invoice Status chart initialized');
        } catch (error) {
            console.error('Error initializing Invoice Status chart:', error);
        }
    }

    function initializeExpiringChart() {
        const chartDom = document.getElementById('expiringChart');
        if (!chartDom) return;

        try {
            const isDark = document.documentElement.classList.contains('dark');
            
            charts.expiring = echarts.init(chartDom);
            
            // Get chart data from global variable or PHP
            const chartData = window.expiringChartData || @json($chartData);
            
            const option = {
                dataset: {
                    source: chartData
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
                        color: ['#e7000b', '#FFD700', '#65B581']
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

            charts.expiring.setOption(option);
            console.log('Expiring chart initialized');
        } catch (error) {
            console.error('Error initializing Expiring chart:', error);
        }
    }

    // Resize handler
    function handleResize() {
        Object.values(charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                try {
                    chart.resize();
                } catch (error) {
                    console.error('Error resizing chart:', error);
                }
            }
        });
    }

    // Debounced resize handler
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    const debouncedResize = debounce(handleResize, 250);

    // Cleanup function
    function cleanupCharts() {
        Object.values(charts).forEach(chart => {
            if (chart && typeof chart.dispose === 'function') {
                try {
                    chart.dispose();
                } catch (error) {
                    console.error('Error disposing chart:', error);
                }
            }
        });
        charts = {};
        isInitialized = false;
    }

    // Event listeners for different scenarios
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - initializing charts');
        setTimeout(initializeCharts, 100);
    });

    window.addEventListener('load', function() {
        if (!isInitialized) {
            console.log('Window loaded - initializing charts');
            setTimeout(initializeCharts, 200);
        }
    });

    // Livewire-specific event listeners
    if (window.Livewire) {
        // When navigating to a new page
        document.addEventListener('livewire:navigating', function() {
            console.log('Livewire navigating - cleaning up charts');
            cleanupCharts();
            window.removeEventListener('resize', debouncedResize);
        });

        // When navigation is complete
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated - reinitializing charts');
            setTimeout(() => {
                initializeCharts();
                window.addEventListener('resize', debouncedResize);
            }, 300);
        });

        // When Livewire component updates
        document.addEventListener('livewire:updated', function() {
            console.log('Livewire updated - checking charts');
            setTimeout(() => {
                // Only reinitialize if charts are missing
                const needsReinit = ['monthlySales', 'invoiceStatusChart', 'expiringChart']
                    .some(id => {
                        const element = document.getElementById(id);
                        return element && !charts[id.replace('Chart', '').replace('monthlySales', 'monthlySales')];
                    });
                
                if (needsReinit) {
                    initializeCharts();
                }
            }, 100);
        });
    }

    // Add resize listener initially
    window.addEventListener('resize', debouncedResize);

    // Expose functions globally for debugging
    window.chartDebug = {
        initializeCharts,
        cleanupCharts,
        charts,
        isInitialized: () => isInitialized
    };
</script>

<!-- Add this script tag to pass PHP data to JavaScript -->
<script>
    // Pass PHP data to JavaScript
    window.invoiceStatusData = {
        paid: {{ $invoiceStatusCounts['paid']['count'] ?? 0 }},
        pending: {{ $invoiceStatusCounts['pending']['count'] ?? 0 }},
        overdue: {{ $invoiceStatusCounts['overdue']['count'] ?? 0 }},
        cancelled: {{ $invoiceStatusCounts['cancelled']['count'] ?? 0 }}
    };
    
    window.expiringChartData = @json($chartData);
</script>