<?php

use App\Models\Product;
use App\Models\Invoice;
use App\Models\Stock;
use App\Models\Customer;
use App\Models\Supplier;
use Livewire\Volt\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\GeminiService;

new class extends Component {
    public $totalProducts = 0;
    public $totalInvoices = 0;
    public $expiredStocks = [];
    public $returnedProducts = [];
    public $agingReports = [];
    public $overdueInvoices = [];
    public $invoiceStatusCounts = [];
    public $expiringSoonStocks = [];
    public $chartData = [];
    public $totalExpiredProducts = 0;
    public $currentMonth;
    public $topCustomers = [];
    public $topAgents = [];
    public $topSuppliers = [];
    public $lowStockItems = [];
    public $salesToday = 0;
    public $monthlySales = 0;
    public $fastMovingItems = [];
    public $slowMovingItems = [];
    public $salesTrendData = [];
    public $inventoryValue = 0;
    public $averageOrderValue = 0;
    public $customerRetentionRate = 0;

    public function mount()
    {
        $today = Carbon::today();
        $fiveMonthsAgo = Carbon::now()->startOfMonth()->subMonths(4);
        $this->currentMonth = $today->format('F');
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $thirtyDaysAgo = $today->copy()->subDays(30);
        $ninetyDaysAgo = $today->copy()->subDays(90);

        // Basic counts
        $this->totalProducts = Product::count();
        $this->totalInvoices = Invoice::count();

        // Expired stocks
        $this->expiredStocks = Stock::whereNotNull('expiration_date')->where('expiration_date', '<=', $today)->orderBy('expiration_date', 'desc')->with('product')->get();

        $this->totalExpiredProducts = $this->expiredStocks->count();

        // Aging reports
        $this->agingReports = Invoice::latest('issued_date')->take(5)->get();

        // Overdue invoices
        $this->overdueInvoices = Invoice::where('due_date', '<', $today)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with('customer')
            ->orderBy('due_date', 'asc')
            ->get();

        // Invoice status counts
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

        // Expiring soon stocks for chart
        $stocks = Stock::whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$today, $today->copy()->addDays(7)])
            ->with('product')
            ->get();

        $data = [['score', 'amount', 'product']];

        foreach ($stocks as $stock) {
            $daysToExpire = $today->diffInDays(Carbon::parse($stock->expiration_date), false);
            if ($daysToExpire <= 0) {
                $score = 0;
            } else {
                $score = $daysToExpire;
            }

            $data[] = [$score, $stock->quantity, ($stock->product->product_code ?? '-') . ' - ' . ($stock->product->name ?? $stock->product_name)];
        }

        $this->chartData = $data;

        // Top Customers by Total Spent
        $this->topCustomers = Invoice::whereBetween('issued_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('customer_id, SUM(grand_total) as total_spent')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->take(5)
            ->get();
        // Top Customers by Total Spent
        $this->topAgents = Invoice::whereBetween('issued_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('customer_id, SUM(grand_total) as total_spent')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->take(5)
            ->get();

        // Top Suppliers
        $this->topSuppliers = Stock::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotNull('supplier_id')
            ->selectRaw('supplier_id, COUNT(*) as delivery_count')
            ->groupBy('supplier_id')
            ->orderByDesc('delivery_count')
            ->with('supplier')
            ->take(5)
            ->get();

        // Low stock items
        $this->lowStockItems = Stock::selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->filter(function ($item) {
                return $item->product && $item->total_quantity <= ($item->product->low_stock_value ?? 0);
            })
            ->values();

        // Sales metrics
        $this->salesToday = Invoice::whereDate('issued_date', $today)->where('status', 'paid')->sum('grand_total');

        // Monthly sales for chart
        $this->monthlySales = Invoice::selectRaw(
            "
            DATE_FORMAT(issued_date, '%b') as month,
            SUM(total_amount) as total,
            DATE_FORMAT(issued_date, '%Y-%m') as sort_month
        ",
        )
            ->where('status', 'paid')
            ->whereDate('issued_date', '>=', $fiveMonthsAgo)
            ->groupBy('sort_month', 'month')
            ->orderBy('sort_month')
            ->get();

        // Fast moving items (sold in last 30 days)
        $this->fastMovingItems = DB::table('invoice_items')->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')->join('products', 'invoice_items.stock_id', '=', 'products.id')->where('invoices.issued_date', '>=', $thirtyDaysAgo)->where('invoices.status', 'paid')->selectRaw('products.id, products.name, products.product_code, SUM(invoice_items.quantity) as total_sold')->groupBy('products.id', 'products.name', 'products.product_code')->orderByDesc('total_sold')->take(10)->get();

        // Slow moving items (not sold in last 90 days)
        $recentlySoldProducts = DB::table('invoice_items')->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')->where('invoices.issued_date', '>=', $ninetyDaysAgo)->where('invoices.status', 'paid')->pluck('invoice_items.stock_id')->unique();

        $this->slowMovingItems = Product::whereNotIn('id', $recentlySoldProducts)
            ->with([
                'stocks' => function ($query) {
                    $query->selectRaw('product_id, SUM(quantity) as total_quantity')->groupBy('product_id');
                },
            ])
            ->take(10)
            ->get();

        // Sales trend data (last 7 days)
        $this->salesTrendData = Invoice::where('issued_date', '>=', $today->copy()->subDays(7))
            ->where('status', 'paid')
            ->selectRaw('DATE(issued_date) as date, SUM(grand_total) as daily_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Inventory value
        $this->inventoryValue = Stock::join('products', 'stocks.product_id', '=', 'products.id')->where('stocks.quantity', '>', 0)->sum(DB::raw('stocks.quantity * products.selling_price'));

        // Average order value
        $this->averageOrderValue = Invoice::where('status', 'paid')->where('issued_date', '>=', $startOfMonth)->avg('grand_total') ?? 0;

        // Customer retention rate (simplified)
        $totalCustomers = Customer::count();
        $repeatCustomers = Invoice::where('issued_date', '>=', $startOfMonth)->select('customer_id')->groupBy('customer_id')->havingRaw('COUNT(*) > 1')->count();

        $this->customerRetentionRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0;
    }
};
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <h1 class="font-bold sm:text-base md:text-lg lg:text-xl">
        Dashboard
    </h1>

    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <x-stat-card :value="'₱' . number_format($salesToday, 2)" label="Income Sales Today" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent) dark:bg-(--color-accent-3-dark)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                    d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
            </svg>
        </x-stat-card>

        <x-stat-card :value="'₱' . number_format($inventoryValue, 2)" label="Inventory Value" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent) dark:bg-(--color-accent-3-dark)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m-6 8a2 2 0 1 0 0-4m0 4a2 2 0 1 1 0-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 1 0 0-4m0 4a2 2 0 1 1 0-4m0 4v2m0-6V4" />
            </svg>
        </x-stat-card>

        <x-stat-card :value="'₱' . number_format($averageOrderValue, 2)" label="Avg Order Value" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent) dark:bg-(--color-accent-3-dark)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m-8.5-9h.01" />
            </svg>
        </x-stat-card>

        <x-stat-card :value="$customerRetentionRate . '%'" label="Customer Retention" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent) dark:bg-(--color-accent-3-dark)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 11.5V15m0 0-2-2m2 2 2-2M7 4V2m10 2V2M5 20h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1Z" />
            </svg>
        </x-stat-card>
    </div>

    <!-- Second Stats Row -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <x-stat-card :value="$totalProducts" label="Total Products" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent) dark:bg-(--color-accent-3-dark)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                    d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm16 14a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2ZM4 13a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6Zm16-2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6Z" />
            </svg>
        </x-stat-card>

        <x-stat-card :value="$totalInvoices" label="Total Invoices" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent) dark:bg-(--color-accent-3-dark)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1ZM8 12v6h8v-6H8Z" />
            </svg>
        </x-stat-card>

        <x-stat-card :value="$totalExpiredProducts" label="Expired Products" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent-2)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                    d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </x-stat-card>

        <x-stat-card :value="count($lowStockItems)" label="Low Stock Items" cardColor="bg-white" iconColor="text-white"
            iconBackgroundColor="bg-(--color-accent-2)">
            <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
            </svg>
        </x-stat-card>
    </div>

    <div>
        <h1 class="font-bold sm:text-base md:text-lg lg:text-xl">
            Reports & Analytics
        </h1>
    </div>

     <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @can('dashboard.view-invoice-status-chart')
            <div
                class="h-full overflow-hidden rounded-md shadow border-neutral-200  bg-white dark:bg-(--color-accent-dark)">
                <div class="mb-4 p-4 rounded-t-md bg-gray-50 dark:bg-(--color-accent-4-dark) dark:rounded-t-sm">
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">Invoice Status</h3>
                </div>
                <div class="flex flex-col items-center pb-10">
                    <div id="invoiceStatusChart" class="-mb-5 mt-8 md:py-0" style="width: 100%; height: 300px;"></div>
                    <div class="flex flex-row gap-5 text-xs px-10">
                        <div class="flex items-center gap-2">
                            <div class="inline-block w-3 h-3 aspect-square rounded-full bg-green-600"></div>
                            <span class="text-gray-700 dark:text-gray-300">Paid:
                                {{ $invoiceStatusCounts['paid']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="inline-block w-3 h-3 aspect-square rounded-full bg-(--color-yellow-400)"></div>
                            <span class="text-gray-700 dark:text-gray-300">Pending:
                                {{ $invoiceStatusCounts['pending']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="inline-block w-3 h-3 aspect-square rounded-full bg-[var(--color-accent-2)]"></div>
                            <span class="text-gray-700 dark:text-gray-300">Overdue:
                                {{ $invoiceStatusCounts['overdue']['percent'] }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="inline-block w-3 h-3 aspect-square rounded-full bg-orange-400"></div>
                            <span class="text-gray-700 dark:text-gray-300">Cancelled:
                                {{ $invoiceStatusCounts['cancelled']['percent'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('dashboard.view-expiring-products-soon')
            <div
                class="h-full overflow-hidden rounded-md shadow border-neutral-200  bg-white dark:bg-(--color-accent-dark)">
                <div class="mb-4 p-4 rounded-t-md bg-gray-50 dark:bg-(--color-accent-4-dark) dark:rounded-t-sm">
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">Expiring Products Soon
                    </h3>
                </div>
                @if (count($chartData) <= 1)
                    <h3 class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                        No products expiring within 7 days.
                    </h3>
                @else
                    <div class="px-10 md:px-10 -mt-5 pb-10 sm:mt-0 sm:pb-2" id="expiringChart"
                        style="width: 100%; height: 320px;"></div>
                @endif
            </div>
        @endcan
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Sales Chart -->
        @can('dashboard.view-monthly-sales-trend')
            <div
                class="h-full overflow-hidden rounded-md shadow border-neutral-200 bg-white dark:bg-(--color-accent-dark)">
                <div class="mb-4 p-4 rounded-t-md bg-gray-50 dark:bg-(--color-accent-4-dark) dark:rounded-t-sm">
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">Monthly Sales Trend</h3>
                </div>
                <div id="monthlySales" class="w-full h-80 py-6 md:py-0 px-6"></div>
            </div>
        @endcan

        <!-- Sales Performance Chart -->
        @can('dashboard.view-7-days-sales-performance')
            <div
                class="h-full overflow-hidden rounded-md shadow border-neutral-200 bg-white dark:bg-(--color-accent-dark)">
                <div class="mb-4 p-4 rounded-t-md bg-gray-50 dark:bg-(--color-accent-4-dark) dark:rounded-t-sm">
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">7-Day Sales Performance
                    </h3>
                </div>
                <div id="salesPerformance" class="w-full h-80 py-6 md:py-0 px-6"></div>
            </div>
        @endcan
    </div>

    <!-- Inventory Analytics Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Fast Moving Items -->
        @can('dashboard.view-fast-moving-items')
            <div
                class="h-full overflow-hidden rounded-md shadow border-neutral-200 bg-white dark:bg-(--color-accent-dark)">
                <div class="mb-4 p-4 rounded-t-md bg-gray-50 dark:bg-(--color-accent-4-dark) dark:rounded-t-sm">
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">Fast Moving Items (Last
                        30 Days)</h3>
                </div>
                <div class="p-4">
                    @if (count($fastMovingItems) > 0)
                        <div class="space-y-3">
                            @foreach ($fastMovingItems as $index => $item)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-(--color-accent-4-dark) rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-green-500 rounded-full">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $item->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->product_code }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-green-600">{{ $item->total_sold }} sold</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No fast moving items data available
                        </div>
                    @endif
                </div>
            </div>
        @endcan

        <!-- Slow Moving Items -->
        @can('dashboard.view-slow-moving-items')

            <div
                class="h-full overflow-hidden rounded-md shadow border-neutral-200 bg-white dark:bg-(--color-accent-dark)">
                <div class="mb-4 p-4 rounded-t-md bg-gray-50 dark:bg-(--color-accent-4-dark) dark:rounded-t-sm">
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-white">Slow Moving Items (90+
                        Days)</h3>
                </div>
                <div class="p-4">
                    @if (count($slowMovingItems) > 0)
                        <div class="space-y-3">
                            @foreach ($slowMovingItems as $index => $item)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-(--color-accent-4-dark) rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-orange-500 rounded-full">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $item->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->product_code }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Stock: {{ $item->stocks->sum('total_quantity') ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No slow moving items found
                        </div>
                    @endif
                </div>
            </div>
        @endcan
    </div>

    <!-- Data Tables Section -->
    <div class="flex flex-col gap-6">
        <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
            @can('dashboard.view-over-due-invoices')
                <x-reports-data-table title="Overdue Invoices" :headers="['Invoice #', 'Customer', 'Due']"
                    emptyMessage="There are currently no overdue invoices." :rows="$overdueInvoices
                        ->map(
                            fn($invoice) => [
                                $invoice->invoice_number,
                                $invoice->customer->name ?? 'Unknown Customer',
                                \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y'),
                            ],
                        )
                        ->toArray()" :rowColors="$overdueInvoices
                        ->map(
                            fn($invoice) => [
                                '',
                                '',
                                'font-semibold text-(--color-accent-2) dark:text-red-300 bg-(--color-accent-2-muted) dark:bg-red-900',
                            ],
                        )
                        ->toArray()" />
            @endcan

            @can('dashboard.view-low-stock-items')
                <x-reports-data-table title="Low Stock Items" description="Items below low stock value" :headers="['Code', 'Name', 'Qty', 'Low']"
                    :rows="$lowStockItems
                        ->map(
                            fn($item) => [
                                $item->product->product_code ?? '-',
                                $item->product->name ?? 'Unknown Product',
                                $item->total_quantity,
                                $item->product->low_stock_value ?? '-',
                            ],
                        )
                        ->toArray()" :rowColors="$lowStockItems
                        ->map(
                            fn($item) => [
                                '',
                                '',
                                'font-semibold text-(--color-accent-2) dark:text-red-300 bg-(--color-accent-2-muted) dark:bg-red-900',
                                'font-semibold text-(--color-accent-2) dark:text-red-300 bg-(--color-accent-2-muted) dark:bg-red-900',
                            ],
                        )
                        ->toArray()" />
            @endcan

            @can('dashboard.view-expired-products')
                <x-reports-data-table title="Expired Products" description="Products past their expiration date"
                    :headers="['Code', 'Name', 'Qty', 'Expiration']" :rows="$expiredStocks
                        ->map(
                            fn($stock) => [
                                $stock->product->product_code ?? '-',
                                $stock->product->name ?? 'Unknown Product',
                                $stock->quantity . ' ' . ($stock->product?->unit?->name ?? ''),
                                \Carbon\Carbon::parse($stock->expiration_date)->format('M d, Y'),
                            ],
                        )
                        ->toArray()" :rowColors="$expiredStocks
                        ->map(
                            fn($stock) => [
                                '',
                                '',
                                '',
                                'font-semibold text-(--color-accent-2) dark:text-red-300 bg-(--color-accent-2-muted) dark:bg-red-900',
                            ],
                        )
                        ->toArray()" />
            @endcan

            @can('dashboard.view-top-suppliers')
                <x-reports-data-table-with-button styleAttributes="md:row-start-1 md:col-start-1"
                    title="Top Suppliers of {{ $currentMonth }}" description="Based on Total Deliveries"
                    :headers="['#', 'Supplier Name', 'Deliveries']" :rows="$topSuppliers->map(
                        fn($supplier, $i) => [
                            $supplier->supplier->name ?? 'Unknown Supplier',
                            $supplier->delivery_count,
                            $i + 1,
                        ],
                    )"
                    emptyMessage="No suppliers found for the month of {{ $currentMonth }}." buttonLabel="All Suppliers"
                    route="suppliers" />
            @endcan
        </div>

        @can('dashboard.view-aging-reports')
            <x-reports-data-table-with-status title="Aging Reports" :headers="['Agent', 'Inovice #', 'Total Amount', 'Status']" :rows="$agingReports->map(
                fn($report) => [
                    $report->agent_id ?? 'null',
                    $report->invoice_number,
                    '₱' . number_format($report->total_amount, 2),
                    $report->status,
                ],
            )"
                route="agingreports" />
        @endcan


        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @can('dashboard.view-top-customers')
                <x-reports-data-table-with-button styleAttributes="md:row-start-2 md:col-start-1"
                    description="Based on Total Spent" title="Top Customers of {{ $currentMonth }}" :headers="['#', 'Customer Name', 'Grand Total']"
                    :rows="$topCustomers->map(
                        fn($customer, $i) => [
                            $i + 1,
                            $customer->customer->name ?? 'Unknown Customer',
                            '₱' . number_format($customer->total_spent, 2),
                        ],
                    )" emptyMessage="No suppliers found for the month of {{ $currentMonth }}"
                    buttonLabel="All Customers" route="customers" />
            @endcan

            @can('dashboard.view-top-agents')
                <x-reports-data-table-with-button styleAttributes="md:row-start-2 md:col-start-2"
                    description="Based on Total Sales" title="Top Agents of {{ $currentMonth }}" :headers="['#', 'Agent Name', 'Total Sales']"
                    :rows="$topAgents->map(
                        fn($agent, $i) => [
                            $i + 1,
                            $agent->agent->name ?? 'Unknown Agent',
                            '₱' . number_format($agent->total_sales, 2),
                        ],
                    )" emptyMessage="No agents found for the month of {{ $currentMonth }}"
                    buttonLabel="All Agents" route="agents" />
            @endcan
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
    let charts = {};
    let isInitialized = false;

    window.monthlySalesData = @json($monthlySales);
    window.salesTrendData = @json($salesTrendData);

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
        waitForElement('salesPerformance', () => initializeSalesPerformance());
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

            const salesData = window.monthlySalesData || [];
            const labels = salesData.map(item => item.month);
            const values = salesData.map(item => item.total);

            charts.monthlySales = echarts.init(chartDom);

            const option = {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        const data = params[0];
                        return `Monthly Sales: ₱${Number(data.value).toLocaleString()}`;
                    }
                },
                textStyle: {
                    fontFamily: fontSans
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    data: values,
                    type: 'line',
                    smooth: true,
                    lineStyle: {
                        color: accentColor
                    },
                    itemStyle: {
                        color: accentColor
                    }
                }]
            };

            charts.monthlySales.setOption(option);
            console.log('Monthly Sales chart initialized');
        } catch (error) {
            console.error('Error initializing Monthly Sales chart:', error);
        }
    }

    function initializeSalesPerformance() {
        const chartDom = document.getElementById('salesPerformance');
        if (!chartDom) return;

        try {
            const accentColor = getCSSVariable('--color-accent') || '#ef4444';
            const fontSans = getCSSVariable('--font-sans') || 'system-ui';

            const salesData = window.salesTrendData || [];
            const labels = salesData.map(item => new Date(item.date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            }));
            const values = salesData.map(item => item.daily_sales);

            charts.salesPerformance = echarts.init(chartDom);

            const option = {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        const data = params[0];
                        return `Sales: ₱${Number(data.value).toLocaleString()}`;
                    }
                },
                textStyle: {
                    fontFamily: fontSans
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    data: values,
                    type: 'bar',
                    itemStyle: {
                        color: accentColor
                    }
                }]
            };

            charts.salesPerformance.setOption(option);
            console.log('Sales Performance chart initialized');
        } catch (error) {
            console.error('Error initializing Sales Performance chart:', error);
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

            const {
                paid,
                pending,
                overdue,
                cancelled
            } = invoiceData;
            const total = paid + pending + overdue + cancelled;

            const colours = {
                paid: '#00a63e',
                pending: yellowColor,
                overdue: accentColor,
                cancelled: '#fb923c'
            };

            let data = [];
            if (total === 0) {
                data = [{
                    value: 1,
                    name: 'No Invoice',
                    itemStyle: {
                        color: '#d1d5db'
                    }
                }];
            } else {
                data = [{
                        value: paid,
                        name: 'Paid',
                        itemStyle: {
                            color: colours.paid
                        },
                        emphasis: {
                            itemStyle: {
                                color: colours.paid
                            }
                        }
                    },
                    {
                        value: pending,
                        name: 'Pending',
                        itemStyle: {
                            color: colours.pending
                        },
                        emphasis: {
                            itemStyle: {
                                color: colours.pending
                            }
                        }
                    },
                    {
                        value: overdue,
                        name: 'Overdue',
                        itemStyle: {
                            color: colours.overdue
                        },
                        emphasis: {
                            itemStyle: {
                                color: colours.overdue
                            }
                        }
                    },
                    {
                        value: cancelled,
                        name: 'Cancelled',
                        itemStyle: {
                            color: colours.cancelled
                        },
                        emphasis: {
                            itemStyle: {
                                color: colours.cancelled
                            }
                        }
                    }
                ];
            }

            const option = {
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    left: 'center',
                    textStyle: {
                        color: isDark ? '#fff' : '#222'
                    }
                },
                textStyle: {
                    fontFamily: fontSans
                },
                series: [{
                    name: 'Invoice Status',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 5,
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: data
                }]
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
                xAxis: {
                    name: 'Qty'
                },
                yAxis: {
                    type: 'category'
                },
                visualMap: {
                    orient: 'horizontal',
                    left: 'center',
                    min: 0,
                    max: 7,
                    text: ['Expiring in 7 days', 'Expiring Soon'],
                    dimension: 0,
                    inRange: {
                        color: ['#e7000b', '#FFD700', '#00a63e']
                    },
                    textStyle: {
                        color: isDark ? '#fff' : '#222'
                    }
                },
                series: [{
                    type: 'bar',
                    encode: {
                        x: 'amount',
                        y: 'product'
                    }
                }]
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
                const needsReinit = ['monthlySales', 'salesPerformance', 'invoiceStatusChart',
                        'expiringChart'
                    ]
                    .some(id => {
                        const element = document.getElementById(id);
                        return element && !charts[id.replace('Chart', '').replace('monthlySales',
                            'monthlySales').replace('salesPerformance', 'salesPerformance')];
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

    // Pass PHP data to JavaScript
    window.invoiceStatusData = {
        paid: {{ $invoiceStatusCounts['paid']['count'] ?? 0 }},
        pending: {{ $invoiceStatusCounts['pending']['count'] ?? 0 }},
        overdue: {{ $invoiceStatusCounts['overdue']['count'] ?? 0 }},
        cancelled: {{ $invoiceStatusCounts['cancelled']['count'] ?? 0 }}
    };

    window.expiringChartData = @json($chartData);
</script>
