@props([
    'title',
    'description' => null,
    'searchPlaceholder' => null,
    'items',
    'message'
])

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex flex-col bg-white rounded-lg">
            <div class="bg-gray-50 p-6 flex flex-col rounded-t-lg">
                <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                    {{ $title }}
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                   {{ $description }}
                </span>
            </div>
            <div class="px-8 pb-4">
                <div class="flex my-5">
                    <div class="flex gap-2 items-center">
                        <label for="perPage" class="text-sm text-gray-700 dark:text-gray-300">Per Page:</label>
                         <flux:select wire:model="perPage" id="perPage" :label="__('')" size="md" value="">
                            <flux:select.option value="5">5</flux:select.option>
                            <flux:select.option value="10">10</flux:select.option>
                            <flux:select.option value="25">25</flux:select.option>
                            <flux:select.option value="50">50</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="ml-auto w-1/3 relative">
                        <x-search-bar placeholder="{{ $searchPlaceholder }}"/>
                    </div>
                </div>
                <div>
                    @if ($items->isEmpty())

                        @isset($emptyIcon)
                            <div class="flex flex-col items-center justify-center p-8">
                               {{ $emptyIcon }}
                            <p class="mb-2 font-bold text-gray-500 dark:text-gray-400">{{ $message }}</p>
                        @else
                            <div class="flex flex-col items-center justify-center p-8">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            <p class="mb-2 font-bold text-gray-500 dark:text-gray-400">{{ $message }}</p>
                        </div>
                        @endisset
                       
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