@props([
    'value',
    'label',
    'cardColor' => 'bg-white',
    'iconColor' => 'text-white',
    'iconBackgroundColor' => 'bg-[#358DBE]',
])

<div class="relative p-6 {{ $cardColor }} rounded-md shadow-sm text-gray-900 dark:bg-gray-800 dark:text-white overflow-hidden">
    <div class="flex items-center gap-4 z-10 relative">
        @if (trim($slot))
            <div class="rounded-sm p-3 {{ $iconColor }} {{ $iconBackgroundColor }}">
                {{ $slot }}
            </div>
        @endif

        <div>
            <p class="text-4xl font-semibold text-black dark:text-white">{{ $value }}</p>
            <h3 class="text-md font-bold text-black dark:text-gray-400">{{ $label }}</h3>
        </div>
    </div>
</div>
