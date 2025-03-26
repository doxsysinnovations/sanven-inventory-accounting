<x-layouts.app title="Inventory Dashboard">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="p-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-purple-500">Total Products</h3>
                        <p class="text-4xl font-semibold text-purple-500">1,500</p>
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
                        <h3 class="text-xl font-bold text-blue-500">Total Orders</h3>
                        <p class="text-4xl font-semibold text-blue-500">1,250</p>
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
                        <h3 class="text-xl font-bold text-green-500">Warehouses</h3>
                        <p class="text-4xl font-semibold text-green-500">8</p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white shadow-lg rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-orange-500">Low Stock Items</h3>
                        <p class="text-4xl font-semibold text-orange-500">24</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">

            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
