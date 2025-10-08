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
        $returnedProducts = \App\Models\StockAlteration::query()->where('type', 'return')->count(); // Count returned products
        $brokenProducts = \App\Models\StockAlteration::query()->where('type', 'broken')->count(); // Count returned products
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
                ],
            ],
            [
                'heading' => 'Sales',
                'items' => [
                    [
                        'icon' => 'document-text',
                        'route' => 'sales-orders',
                        'label' => 'Sales Orders',
                        'permission' => 'purchase-orders.view',
                    ],
                    [
                        'icon' => 'truck',
                        'route' => 'delivery-notes',
                        'label' => 'Delivery Notes',
                        'permission' => 'purchase-orders.view',
                    ],
                ],
            ],

            [
                'heading' => 'Purchasing Management',
                'items' => [
                    [
                        'icon' => 'document-text',
                        'route' => 'purchase-orders',
                        'label' => 'Purchase Orders',
                        'permission' => 'purchase-orders.view',
                    ],
                    // [
                    //     'icon' => 'truck',
                    //     'route' => 'suppliers',
                    //     'label' => 'Suppliers',
                    //     'permission' => 'suppliers.view',
                    // ],
                ],
            ],
            [
                'heading' => 'Invoice Management',
                'items' => [
                    [
                        'icon' => 'plus',
                        'route' => 'invoicing.create',
                        'label' => 'Create Invoice',
                        'permission' => 'agents.create',
                    ],
                    [
                        'icon' => 'document-text',
                        'route' => 'invoicing',
                        'label' => 'Invoicing',
                        'permission' => 'invoicing.view',
                    ],
                ],
            ],
            [
                'heading' => 'Agent Management',
                'items' => [
                    [
                        'icon' => 'user-plus',
                        'route' => 'agents.create',
                        'label' => 'Add Agent',
                        'permission' => 'agents.create',
                    ],
                    [
                        'icon' => 'users',
                        'route' => 'agents',
                        'label' => 'Agents',
                        'permission' => 'agents.view',
                    ],
                ],
            ],
            [
                'heading' => 'Customer Management',
                'items' => [
                    [
                        'icon' => 'user-plus',
                        'route' => 'customers.create',
                        'label' => 'Add Customer',
                        'permission' => 'customers.create',
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
                'heading' => 'Quotation Management',
                'permission' => 'products.view',
                'items' => [
                    [
                        'icon' => 'plus',
                        'route' => 'quotations.create',
                        'label' => 'Create Quotation',
                        'permission' => 'quotations.create',
                    ],
                    [
                        'icon' => 'bolt',
                        'route' => 'quotations',
                        'label' => 'Quotations List',
                        'permission' => 'quotations.view',
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
                        'label' => 'Receive Stock',
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
                        'permission' => 'stocks.view-expiry',
                    ],
                    [
                        'icon' => 'arrow-uturn-left',
                        'route' => 'stocks.returned',
                        'label' => 'Returned Stocks (' . $returnedProducts . ')', // Add the count here
                        'permission' => 'stocks.returned',
                    ],
                    [
                        'icon' => 'exclamation-triangle',
                        'route' => 'stocks.broken',
                        'label' => 'Broken Stocks (' . $brokenProducts . ')', // Add the count here
                        'permission' => 'stocks.broken',
                    ],
                    // [
                    //     'icon' => 'pencil-square',
                    //     'route' => 'purchase-requests',
                    //     'label' => 'Purchase Requests',
                    //     'permission' => 'purchase-requests.view',
                    // ],
                ],
            ],
            [
                'heading' => 'Product Management',
                'permission' => 'products.view',
                'items' => [
                    [
                        'icon' => 'plus',
                        'route' => 'products.create',
                        'label' => 'Create Product',
                        'permission' => 'products.create',
                    ],
                    [
                        'icon' => 'rectangle-group',
                        'route' => 'products',
                        'label' => 'Products List',
                        'permission' => 'products.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'brands.create',
                        'label' => 'Add Brand',
                        'permission' => 'brands.create',
                    ],
                    [
                        'icon' => 'tag',
                        'route' => 'brands',
                        'label' => 'Brands',
                        'permission' => 'brands.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'categories.create',
                        'label' => 'Add Category',
                        'permission' => 'categories.create',
                    ],
                    [
                        'icon' => 'folder',
                        'route' => 'categories',
                        'label' => 'Categories',
                        'permission' => 'categories.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'types.create',
                        'label' => 'Add Type',
                        'permission' => 'types.create',
                    ],
                    [
                        'icon' => 'cube',
                        'route' => 'types',
                        'label' => 'Types',
                        'permission' => 'types.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'units.create',
                        'label' => 'Add Unit',
                        'permission' => 'units.create',
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
                'heading' => 'Agents Management',
                'items' => [
                    [
                        'icon' => 'user-group',
                        'route' => 'agents',
                        'label' => 'Agents',
                        'permission' => 'agents.view',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'agent-commissions',
                        'label' => 'Commissions',
                        'permission' => 'customers.view',
                    ],
                ],
            ],
            [
                'heading' => 'Supplier Management',
                'permission' => 'suppliers.view',
                'items' => [
                    // [
                    //     'icon' => 'plus',
                    //     'route' => 'suppliers.create',
                    //     'label' => 'Add Supplier',
                    //     'permission' => 'suppliers.create',
                    // ],
                    [
                        'icon' => 'truck',
                        'route' => 'suppliers',
                        'label' => 'Suppliers',
                        'permission' => 'suppliers.view',
                    ],
                ],
            ],
            [
                'heading' => 'Accounting',
                'permission' => 'suppliers.view',
                'items' => [
                    [
                        'icon' => 'user-group',
                        'route' => 'chartofaccounts',
                        'label' => 'Chart of Accounts',
                        'permission' => 'suppliers.view',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'agingreports',
                        'label' => 'Aging Reports',
                        'permission' => 'suppliers.view',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'recievables',
                        'label' => 'Receivables',
                        'permission' => 'suppliers.view',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'payables',
                        'label' => 'Account Payables',
                        'permission' => 'suppliers.view',
                    ],
                ],
            ],
            [
                'heading' => 'User Management',
                'permission' => 'users.view',
                'items' => [
                    [
                        'icon' => 'user-plus',
                        'route' => 'users.create',
                        'label' => 'Add User',
                        'permission' => 'users.create',
                    ],
                    [
                        'icon' => 'user-group',
                        'route' => 'users',
                        'label' => 'Users',
                        'permission' => 'users.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'roles.create',
                        'label' => 'Add Role',
                        'permission' => 'roles.create',
                    ],
                    [
                        'icon' => 'shield-check',
                        'route' => 'roles',
                        'label' => 'Roles',
                        'permission' => 'roles.view',
                    ],
                    [
                        'icon' => 'finger-print',
                        'route' => 'audittrail',
                        'label' => 'Audit Trail',
                        'permission' => 'audittrail.view',
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'locations.create',
                        'label' => 'Add Location',
                        'permission' => 'locations.create',
                    ],
                    [
                        'icon' => 'map-pin',
                        'route' => 'locations',
                        'label' => 'Locations',
                        'permission' => 'locations.view',
                    ],
                ],
            ],
            [
                'heading' => 'Special Features',
                'permission' => 'special-features.view',
                'items' => [
                    [
                        'icon' => 'document-text',
                        'route' => 'pdf-binding',
                        'label' => 'PDF Binding',
                        'permission' => 'special-features.pdf-binding-view',
                    ],
                ],
            ],
            [
                'heading' => 'General Settings',
                'permission' => 'general-settings.view',
                'items' => [
                    [
                        'icon' => 'server',
                        'route' => 'database-backup',
                        'label' => 'Database Backup',
                        'permission' => 'database-backup.view',
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

    protected function filterMenuItems(array $menuItems, string $search): array
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

    // Method to check if any route in the group matches current route
    public function isGroupActive(array $group): bool
    {
        return collect($group['items'])->contains(function ($item) {
            return request()->routeIs($item['route'] . '*');
        });
    }
}; ?>

<div>
    <flux:navlist variant="outline" searchable>
        @foreach ($menuItems as $group)
            @if ($group['heading'] === 'Navigations')
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
            @elseif($group['heading'] !== 'Navigations')
                <flux:navlist.group :heading="__($group['heading'])" :expanded="($this->isGroupActive($group))" expandable>
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
            @endif
        @endforeach
    </flux:navlist>
</div>
