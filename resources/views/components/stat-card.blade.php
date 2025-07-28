@props([
    'value',
    'label',
    'cardColor' => 'bg-white',
    'iconColor' => 'text-white',
    'iconBackgroundColor' => 'bg-[#358DBE]',
])

<div class="w-full h-full p-6 {{ $cardColor }} rounded-md shadow-sm text-gray-900 overflow-hidden flex items-center md:justify-center lg:justify-start
        dark:bg-(--color-accent-dark) dark:text-white
    ">
    <div class="flex items-center gap-4 h-full min-w-0">
        @if (trim($slot))
            <div class="rounded-sm p-3 {{ $iconColor }} {{ $iconBackgroundColor }}">
                {{ $slot }}
            </div>
        @endif

        <div class="flex flex-col justify-center h-full min-w-0">
            <p class="text-2xl md:text-3xl lg:text-4xl font-semibold text-black dark:text-white">{{ $value }}</p>
            <h3 class="text-sm md:text-base font-bold text-black dark:text-gray-400 md:truncate">{{ $label }}</h3>
        </div>
    </div>
</div>
