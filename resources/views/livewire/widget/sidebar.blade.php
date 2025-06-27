<?php

use Livewire\Volt\Component;

new class extends Component {
    public $search = '';
    public $menuItems = [];

    public function placeholder()
    {
        return $this->search;
    }

    public function with()
    {
        $expiryStocksCount = \App\Models\Stock::whereDate('expiration_date', '<=', now()->addDays(30))->count(); // Count expiry stocks
        $this->menuItems = [
            [
                'heading' => 'Navigations',
                'items' => [
                    [
                        'icon' => 'home',
                        'route' => 'dashboard',
                        'label' => 'Dashboard',
                        'permission' => 'dashboard.view',
                    ],
                    [
                        'icon' => 'shopping-cart',
                        'route' => 'invoicing',
                        'label' => 'Invoicing',
                        'permission' => 'invoicing.view',
                    ],
                    [
                        'icon' => 'document-text',
                        'route' => 'quotations',
                        'label' => 'Quotations',
                        'permission' => 'quotations.view',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'agents',
                        'label' => 'Agents',
                        'permission' => 'agents.view',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'customers',
                        'label' => 'Customers',
                        'permission' => 'customers.view',
                    ],
                ],
            ],
            [
                'heading' => 'Stock Management',
                'permission' => 'stocks.view',
                'items' => [
                    [
                        'icon' => 'plus',
                        'route' => 'stocks.create',
                        'label' => 'Receive Stocks',
                        'permission' => 'stocks.create',
                    ],
                    [
                        'icon' => 'list-bullet',
                        'route' => 'stocks',
                        'label' => 'Stocks List',
                        'permission' => 'stocks.view',
                    ],
                    [
                        'icon' => 'exclamation-triangle',
                        'route' => 'expiryproducts',
                        'label' => 'Expiry Stocks (' . $expiryStocksCount . ')', // Add the count here
                        'permission' => 'stocks.view',
                    ],
                ],
            ],
            [
                'heading' => 'Products Management',
                'permission' => 'products.view',
                'items' => [
                    [
                        'icon' => 'bolt',
                        'route' => 'products',
                        'label' => 'Products',
                        'permission' => 'products.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'products.create',
                        'label' => 'Add New',
                        'permission' => 'products.create',
                    ],
                    [
                        'icon' => 'tag',
                        'route' => 'brands',
                        'label' => 'Brands',
                        'permission' => 'brands.view',
                    ],
                    [
                        'icon' => 'folder',
                        'route' => 'categories',
                        'label' => 'Categories',
                        'permission' => 'categories.view',
                    ],
                    [
                        'icon' => 'cube',
                        'route' => 'types',
                        'label' => 'Types',
                        'permission' => 'types.view',
                    ],
                    [
                        'icon' => 'scale',
                        'route' => 'units',
                        'label' => 'Units',
                        'permission' => 'units.view',
                    ],
                ],
            ],
            [
                'heading' => 'Supplier Management',
                'permission' => 'suppliers.view',
                'items' => [
                    [
                        'icon' => 'user-group',
                        'route' => 'suppliers',
                        'label' => 'Suppliers',
                        'permission' => 'suppliers.view',
                    ],
                ],
            ],
            [
                'heading' => 'Reporting',
                'permission' => 'suppliers.view',
                'items' => [
                    [
                        'icon' => 'user-group',
                        'route' => 'agingreports',
                        'label' => 'Aging Reports',
                        'permission' => 'suppliers.view',
                    ],
                ],
            ],
            [
                'heading' => 'User Management',
                'permission' => 'users.view',
                'items' => [
                    [
                        'icon' => 'user-group',
                        'route' => 'users',
                        'label' => 'Users',
                        'permission' => 'users.view',
                    ],
                    [
                        'icon' => 'shield-check',
                        'route' => 'roles',
                        'label' => 'Roles',
                        'permission' => 'roles.view',
                    ],
                    [
                        'icon' => 'clipboard-document-list',
                        'route' => 'audittrail',
                        'label' => 'Audit Trail',
                        'permission' => 'audittrail.view',
                    ],
                    [
                        'icon' => 'map-pin',
                        'route' => 'locations',
                        'label' => 'Locations',
                        'permission' => 'locations.view',
                    ],
                ],
            ],
        ];

        // Filter menu items based on search input
        $filteredMenuItems = $this->filterMenuItems($this->menuItems, $this->search);

        return [
            'menuItems' => $filteredMenuItems,
        ];
    }

    protected function filterMenuItems($menuItems, $search)
    {
        return collect($menuItems)
            ->map(function ($group) use ($search) {
                $filteredItems = collect($group['items'])
                    ->filter(function ($item) use ($search) {
                        return stripos($item['label'], $search) !== false;
                    })
                    ->values()
                    ->toArray();

                return array_merge($group, ['items' => $filteredItems]);
            })
            ->filter(function ($group) {
                return !empty($group['items']);
            })
            ->values()
            ->toArray();
    }
}; ?>

<div>
    <flux:navlist variant="outline" searchable>
        {{-- <flux:input type="search" placeholder="Search navigation..." class="mb-4" wire:model.live="search" /> --}}

        @foreach ($menuItems as $group)
        <flux:navlist.group :heading="__($group['heading'])" class="grid">
            @if (isset($group['permission']))
                @can($group['permission'])
                    @foreach ($group['items'] as $item)
                        @if (!$item['permission'] || auth()->user()->can($item['permission']))
                            <flux:navlist.item :icon="$item['icon']" :href="route($item['route'])"
                                :current="request()->routeIs($item['route'])" wire:navigate>
                                {{ __($item['label']) }}
                            </flux:navlist.item>
                        @endif
                    @endforeach
                @endcan
            @else
                @foreach ($group['items'] as $item)
                    @if (!$item['permission'] || auth()->user()->can($item['permission']))
                        <flux:navlist.item :icon="$item['icon']" :href="route($item['route'])"
                            :current="request()->routeIs($item['route'])" wire:navigate>
                            {{ __($item['label']) }}
                        </flux:navlist.item>
                    @endif
                @endforeach
            @endif
        </flux:navlist.group>
    @endforeach
    </flux:navlist>
</div>
