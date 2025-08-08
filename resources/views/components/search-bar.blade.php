@props([
  'placeholder' => 'Search...'  
])

<div class="w-full lg:w-100">
    <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" 
            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500 dark:text-gray-400" 
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
        </svg>
                
        <input
            wire:model.live="search"
            type="search"
            placeholder="{{ $placeholder }}"
            aria-label="{{ $placeholder }}"
            class="w-full rounded border border-gray-300 bg-white 
                dark:bg-gray-800 pl-10 pr-4 py-2.5 text-sm text-gray-900 
                dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 
                focus:border-[var(--color-accent)] dark:focus:border-[var(--color-accent)]
                focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-300
                focus:outline-none dark:border-gray-600"
        />
    </div>
</div>