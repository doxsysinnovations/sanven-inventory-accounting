<x-layouts.app title="Inventory Dashboard">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1>
             Dashboard
        </h1>
      
        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="p-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <div>
                        <h3 class="text-MD font-bold text-indigo-500">Income Sales Today</h3>
                        <p class="text-4xl font-semibold text-indigo-500">₱104,000</p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <div>
                        <h3 class="text-MD font-bold text-purple-500">Total Products</h3>
                        <p class="text-4xl font-semibold text-purple-500">150</p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <div>
                        <h3 class="text-MD font-bold text-blue-500">Total Invoices</h3>
                        <p class="text-4xl font-semibold text-blue-500">320</p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                    <div>
                        <h3 class="text-MD font-bold text-green-500">Expired Products</h3>
                        <p class="text-4xl font-semibold text-green-500">25</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Three Columns -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Due Invoices -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Due Invoices</h3>
                <ul class="space-y-2">
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Invoice #1001 - ABC Hospital</span>
                        <span class="text-sm text-red-500 font-semibold">Apr 30, 2025</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Invoice #1002 - XYZ Clinic</span>
                        <span class="text-sm text-red-500 font-semibold">May 5, 2025</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Invoice #1003 - HealthCare Plus</span>
                        <span class="text-sm text-red-500 font-semibold">May 10, 2025</span>
                    </li>
                </ul>
            </div>
            
            <!-- Returned/Rejected Products -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Returned/Rejected Products</h3>
                <ul class="space-y-2">
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Surgical Gloves (Box of 100)</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">10 boxes returned</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Face Masks (Box of 50)</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">5 boxes returned</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Digital Thermometers</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">3 units rejected</span>
                    </li>
                </ul>
            </div>
            
            <!-- Expired Products -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Expired Products</h3>
                <ul class="space-y-2">
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Paracetamol (500mg) - 100 Tablets</span>
                        <span class="text-sm text-red-500 font-semibold">Apr 20, 2025</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Vitamin C (1000mg) - 50 Tablets</span>
                        <span class="text-sm text-red-500 font-semibold">Apr 25, 2025</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Insulin Syringes (Box of 100)</span>
                        <span class="text-sm text-red-500 font-semibold">Apr 28, 2025</span>
                    </li>
                </ul>
            </div>
        </div>

         <!-- Aging Reports Table -->
         <div class="mt-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Aging Reports</h3>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Agent</th>
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Invoice #</th>
                        <th class="py-2 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Total Amount</th>
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
    </div>
</x-layouts.app>