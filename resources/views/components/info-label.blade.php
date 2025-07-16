@props([
    'label',
    'value' => null,
])

<div>
    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
        {{ $label }}
    </span>
    <div class="text-s text-gray-900 dark:text-gray-100">
        {{ $value ?? 'N/A' }}
    </div>
</div>