@props([
    'value',
    'label',
    'iconBackgroundColor' => 'bg-[#358DBE]', 
])

<div class="p-6 bg-white shadow rounded-xl text-gray-900 dark:bg-gray-800 dark:text-white">
    <div class="flex items-center gap-4">
        @if (trim($slot)) {{-- Only show icon container if something was passed in the slot --}}
            <div class="rounded-sm p-3 {{ $iconBackgroundColor }}">
                {{ $slot }}
            </div>
        @endif

        <div>
            <p class="text-4xl font-semibold text-black">{{ $value }}</p>
            <h3 class="text-md font-bold text-black-500">{{ $label }}</h3>
        </div>
    </div>
</div>
