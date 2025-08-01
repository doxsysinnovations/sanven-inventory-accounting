@props([
    'title',
    'description' => null,
    'withSearch' => true,
    'withDateFilter' => false,
    'withRoleFilter' => false,
    'withPaymentMethodFilter' => false,
    'withStatusFilter' => false,
    'searchPlaceholder' => null,
    'withFilter' => false,
    'filterItems',
    'items',
    'message',
    'perPage' => 25,
    'showNewCreateButtonIfEmpty' => false,
    'createButtonLabel' => 'Add',
    'createButtonAbility' => '',
    'createButtonRoute' => '',
    'createButtonLabelIfEmpty' => null,
    'createButtonAbilityIfEmpty' => null,
    'createButtonRouteIfEmpty' => null,
])

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex flex-col bg-white dark:bg-(--color-accent-dark) rounded-lg">
            <div class="bg-gray-50 px-6 py-8 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0 overflow-auto rounded-t-lg dark:bg-(--color-accent-4-dark) dark:text-white">
                <div>
                    <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-gray-100">
                        {{ $title }}
                    </h3>
                    <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        {{ $description }}
                    </span>
                </div>

                @if (!$items->isEmpty() || $showNewCreateButtonIfEmpty)
                    @php
                        $ability = $items->isEmpty()
                            ? ($createButtonAbilityIfEmpty ?? $createButtonAbility)
                            : $createButtonAbility;
                        
                        $route = $items->isEmpty()
                            ? ($createButtonRouteIfEmpty ?? $createButtonRoute)
                            : $createButtonRoute;
                    @endphp

                    @can($ability)
                        <div>
                            <a href="{{ route($route) }}">
                                <flux:button variant="primary" color="blue" icon="plus">
                                    {{ $items->isEmpty() ? ($createButtonLabelIfEmpty ?? $createButtonLabel) : $createButtonLabel }}
                                </flux:button>
                            </a>
                        </div>
                    @endcan
                @endif
            </div>

            <div class="px-10 pt-4 pb-10 overflow-auto">
                <div class="flex flex-col gap-4 my-5">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="perPage" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">Per Page:</label>
                            <flux:select wire:model.live="perPage" id="perPage" class="flex-1 sm:flex-none">
                                <flux:select.option value="5">5</flux:select.option>
                                <flux:select.option value="10">10</flux:select.option>
                                <flux:select.option value="25">25</flux:select.option>
                                <flux:select.option value="50">50</flux:select.option>
                            </flux:select>
                        </div>

                        @if($withFilter)
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <label for="categoryFilter" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    Category:
                                </label>
                                <flux:select wire:model.live="selectedCategory" id="categoryFilter" class="flex-1 sm:flex-none">
                                    <flux:select.option value="">All Categories</flux:select.option>
                                    @foreach ($filterItems as $filterItem)
                                        <flux:select.option value="{{ $filterItem->id }}">{{ $filterItem->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                        @endif

                        @if($withRoleFilter)
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <label for="roleFilter" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    Role:
                                </label>
                                <flux:select wire:model.live="selectedUserType" id="roleFilter" size="md" class="flex-1 sm:flex-none">
                                    <flux:select.option value="">All</flux:select.option>
                                    <flux:select.option value="superadmin">Super Admin</flux:select.option>
                                    <flux:select.option value="admin">Admin</flux:select.option>
                                    <flux:select.option value="staff">Staff</flux:select.option>
                                    <flux:select.option value="agent">Agent</flux:select.option>
                                </flux:select>
                            </div>
                        @endif
                    </div>

                    @if($withPaymentMethodFilter || $withStatusFilter || $withSearch || $withDateFilter)
                        <div class="flex flex-col sm:flex-row gap-4 sm:justify-between">
                            <div class="flex flex-col sm:flex-row gap-4 flex-wrap mb-5 {{ !($withPaymentMethodFilter || $withStatusFilter) ? 'hidden' : '' }}">
                                @if($withPaymentMethodFilter)
                                    <div class="flex items-center gap-2 w-full sm:w-auto sm:min-w-fit">
                                        <label for="paymentMethodFilter" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            Payment Method:
                                        </label>
                                        <flux:select wire:model.live="paymentMethod" id="paymentMethodFilter" class="flex-1 sm:flex-none">
                                            <flux:select.option value="">All Methods</flux:select.option>
                                            <flux:select.option value="cash">Cash</flux:select.option>
                                            <flux:select.option value="credit_card">Credit Card</flux:select.option>
                                            <flux:select.option value="bank_transfer">Bank Transfer</flux:select.option>
                                            <flux:select.option value="paypal">PayPal</flux:select.option>
                                            <flux:select.option value="other">Other</flux:select.option>
                                        </flux:select>
                                    </div>
                                @endif

                                @if($withStatusFilter)
                                    <div class="flex items-center gap-2 w-full sm:w-auto sm:min-w-fit">
                                        <label for="statusFilter" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            Status:
                                        </label>
                                        <flux:select wire:model.live="status" id="statusFilter" class="flex-1 sm:flex-none">
                                            <flux:select.option value="">All Statuses</flux:select.option>
                                            <flux:select.option value="pending">Pending</flux:select.option>
                                            <flux:select.option value="paid">Paid</flux:select.option>
                                            <flux:select.option value="overdue">Overdue</flux:select.option>
                                            <flux:select.option value="cancelled">Cancelled</flux:select.option>
                                        </flux:select>
                                    </div>
                                @endif
                            </div>

                            <div class="{{ !$withDateFilter ? 'hidden' : '' }}">
                                @if($withDateFilter)
                                    <div class="flex items-end gap-3 @if($withSearch && $withDateFilter) h-[42px] @endif">
                                        <div class="w-full sm:w-auto">
                                            <x-flux::input wire:model.live="startDate" type="date" label="Start" placeholder="Start" />
                                        </div>
                                        <div class="w-full sm:w-auto">
                                            <x-flux::input wire:model.live="endDate" type="date" label="End" placeholder="End" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="{{ !$withSearch ? 'hidden' : '' }}">
                            @if($withSearch)
                                <div class="flex items-center justify-end w-full">
                                    <x-search-bar placeholder="{{ $searchPlaceholder }}" />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                
                <div class="mt-8">
                    {{ $statisticsSlot ?? '' }}
                </div>

                <div>
                    @if ($items->isEmpty())
                        <div class="flex flex-col items-center justify-center p-10">
                            @isset($emptyIcon)
                                {{ $emptyIcon }}
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-30 h-30 sm:w-48 sm:h-48 mb-2 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            @endisset

                            <p class="mb-2 font-bold text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 shadow-xs">
                            {{ $slot }}
                        </div>

                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>