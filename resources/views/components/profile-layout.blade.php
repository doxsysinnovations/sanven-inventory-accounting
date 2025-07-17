@props([    
    'title' => 'Profile',
    'modelInstance',
    'headerDetails' => [],
    'labelKey' => 'label',
    'valueKey' => 'value',
    'modelInfo',
    'editRoute',
    'activeTab',
    'tabs' => [],
    'tabContents' => [],
    'emptyState' => [
        'title' => 'No data available.',
        'description' => 'Content will appear here when available.',
    ],
    'withStatus' => false,
    'status' => null,
    'locationsConfig' => null // New prop for locations configuration
])

@php
    $activeTabData = $tabContents[$activeTab] ?? [];
@endphp

<div>
    <div class="bg-white rounded-lg dark:bg-gray-900 sm:pb-4">
         <div class="bg-gray-50 p-6 flex flex-col rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                {{ $title }}
            </h3>
        </div>

        <div class="flex flex-col px-4 pt-5 pb-4 sm:p-6">
            <div class="flex justify-between">
                <div class="flex items-center gap-5">
                    <div>
                        <div class="h-35 w-35 rounded-full border-4 border-white dark:border-gray-800 bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                            <svg class="h-full w-full text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col mt-2 gap-4">
                        <div class="flex items-center gap-x-5">
                            <div>
                                <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $modelInfo->name }}
                                </h3>
                            </div>
                            @if ($withStatus)
                                <div>
                                    <x-status-badges :status="$status" />
                                </div>
                            @endif
                        </div>

                     @if (!empty($headerDetails))
                        <div class="flex gap-10">
                            @foreach ($headerDetails as $details)
                                <x-info-label 
                                    :label="$details['label'] ?? '—'" 
                                    :value="$details['value'] ?? '—'" 
                                />
                            @endforeach
                        </div>
                    @else
                        <span class="text-sm text-gray-900 dark:text-gray-100">No details provided.</span>
                    @endif

                    </div>
                </div> 
                <div>
                    <a href="{{ route($editRoute, [$modelInstance => $modelInfo->id]) }}">
                        <flux:button variant="primary" icon="pencil">Edit Profile</flux:button>                                
                    </a>
                </div>
            </div>
            <div>
                <div>
                    <div class="mt-6 border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            @foreach ($tabs as $tab)
                                <button wire:click="$set('activeTab', '{{ $tab['key'] }}')"
                                    class="text-(--color-accent) whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold {{ $activeTab === $tab['key'] 
                                        ? 'border-(--color-accent) text-(--color-accent) dark:border-blue-400 dark:text-blue-400' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">

                                    {{ $tab['label'] }}
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    <div class="mt-4">
                        @if ($activeTab === 'locations' && $locationsConfig)
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $locationsConfig['title'] ?? 'Assigned Locations' }}
                                </h4>
                                
                                @if(($locationsConfig['locations'] ?? collect())->count() > 0)
                                    <div class="grid {{ $locationsConfig['gridCols'] ?? 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3' }} gap-3">
                                        @foreach($locationsConfig['locations'] as $location)
                                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $location->name }}</h5>
                                                
                                               <div class="flex flex-col gap-y-1">
                                                    @if(($locationsConfig['showStatus'] ?? true) && isset($location->is_active))
                                                        <div class="items-center inline-flex">
                                                            <span class="pr-1 pt-1 text-xs font-bold text-gray-500 dark:text-gray-400">Status:</span>
                                                            <x-status-badges :status="strtolower($location->is_active ? 'Active' : 'Inactive')" />
                                                        </div>
                                                    @endif
                                                    
                                                    @if(isset($location->address))
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ $location->address }}
                                                        </p>
                                                    @endif
                                                    
                                                    @if(isset($location->pivot) && $location->pivot->created_at)
                                                        <div class="items-center inline-flex">
                                                            <span class="text-xs pr-1 font-bold text-gray-500 dark:text-gray-400">Assigned:</span>
                                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $location->pivot->created_at->format('M d, Y') }}</span>
                                                        </div>
                                                    @endif
                                               </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $locationsConfig['emptyTitle'] ?? 'No locations assigned' }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $locationsConfig['emptyDescription'] ?? 'This item is not assigned to any locations yet.' }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @elseif (count($activeTabData))
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                              @foreach ($activeTabData as $details)
                                @if ($withStatus && strtolower($details['label']) === 'status')
                                    <x-info-label :label="$details['label'] ?? '—'">
                                        <x-status-badges :status="strtolower($details['value'])" />
                                    </x-info-label>
                                @else
                                    <x-info-label 
                                        :label="$details['label'] ?? '—'"
                                        :value="$details['value'] ?? '—'"
                                    />
                                @endif
                            @endforeach
                            </div>  
                        @elseif($activeTab === "history")
                            <div class="rounded-md bg-gray-50 dark:bg-gray-800 p-4">
                                <div class="text-center">
                                    <svg class="mx-auto h-20 w-20 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m10.827 5.465-.435-2.324m.435 2.324a5.338 5.338 0 0 1 6.033 4.333l.331 1.769c.44 2.345 2.383 2.588 2.6 3.761.11.586.22 1.171-.31 1.271l-12.7 2.377c-.529.099-.639-.488-.749-1.074C5.813 16.73 7.538 15.8 7.1 13.455c-.219-1.169.218 1.162-.33-1.769a5.338 5.338 0 0 1 4.058-6.221Zm-7.046 4.41c.143-1.877.822-3.461 2.086-4.856m2.646 13.633a3.472 3.472 0 0 0 6.728-.777l.09-.5-6.818 1.277Z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $emptyState['title'] }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $emptyState['description'] }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="rounded-md bg-gray-50 dark:bg-gray-800 p-4">
                                <div class="text-center">
                                    <svg class="mx-auto h-20 w-20 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $emptyState['title'] }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $emptyState['description'] }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>