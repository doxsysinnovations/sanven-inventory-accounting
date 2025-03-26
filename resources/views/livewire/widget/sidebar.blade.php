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
        $this->menuItems = [
            [
                'heading' => 'Navigations',
                'items' => [
                    [
                        'icon' => 'home',
                        'route' => 'dashboard',
                        'label' => 'Dashboard',
                        'permission' => null,
                    ],
                ],
            ],
            [
                'heading' => 'Products Management',
                'permission' => null,
                'items' => [
                    [
                        'icon' => 'bolt',
                        'route' => 'products',
                        'label' => 'Products',
                        'permission' => null,
                    ],
                    [
                        'icon' => 'plus',
                        'route' => 'products.create',
                        'label' => 'Add New',
                        'permission' => null,
                    ],
                    [
                        'icon' => 'tag',
                        'route' => 'brands',
                        'label' => 'Brands',
                        'permission' => null,
                    ],
                    [
                        'icon' => 'folder',
                        'route' => 'categories',
                        'label' => 'Categories',
                        'permission' => null,
                    ],
                    [
                        'icon' => 'scale',
                        'route' => 'units',
                        'label' => 'Units',
                        'permission' => null,
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
                        'permission' => null,
                    ],
                    [
                        'icon' => 'shield-check',
                        'route' => 'roles',
                        'label' => 'Roles',
                        'permission' => 'roles.view',
                    ],
                ],
            ],
        ];

        // Filter menu items based on search input
        $filteredMenuItems = $this->filterMenuItems($this->menuItems, $this->search);

        return [
            'menuItems' => $filteredMenuItems
        ];
    }

    protected function filterMenuItems($menuItems, $search)
    {
        return collect($menuItems)->map(function ($group) use ($search) {
            $filteredItems = collect($group['items'])->filter(function ($item) use ($search) {
                return stripos($item['label'], $search) !== false;
            })->values()->toArray();

            return array_merge($group, ['items' => $filteredItems]);
        })->filter(function ($group) {
            return !empty($group['items']);
        })->values()->toArray();
    }
}; ?>

<div>
    <flux:navlist variant="outline" searchable>
        <flux:input type="search" placeholder="Search navigation..." class="mb-4" wire:model.live="search" />

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
