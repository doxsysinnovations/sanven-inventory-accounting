@props(['wireModel' => null])

<div x-data="{ open: @entangle($wireModel) }"
     x-show="open"
     class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
     x-on:keydown.escape.window="open = false">
    <div class="fixed inset-0 transform transition-all" x-on:click="open = false">
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg mx-auto"
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="px-6 py-4">
            {{ $title }}
        </div>

        <div class="px-6 py-2">
            {{ $content }}
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 text-right">
            {{ $footer }}
        </div>
    </div>
</div>
