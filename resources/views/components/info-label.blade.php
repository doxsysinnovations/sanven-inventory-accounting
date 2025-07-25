@props([
    'label',
    'value' => null,
])

<div class="flex flex-col">
    <div>
        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
            {{ $label }}
        </span>
    </div>
    <div>
        @if ($slot->isNotEmpty())
           <div class="flex sm:text-sm">
                <div>
                    {{ $slot }}
                </div>
            </div>
        @else
            <span class="text-sm text-gray-900 dark:text-gray-100">
                {{ $value ?? 'N/A' }}
            </span>
        @endif
    </div>
</div>