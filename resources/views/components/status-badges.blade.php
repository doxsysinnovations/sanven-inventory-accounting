@props([
    'date' => null,
    'status',
])

@php
    $statusGroups = [
        'gray' => ['valid'],
        'orange' => ['expiring'],
        'red' => ['expired', 'inactive'],
        'green' => ['accepted', 'active'],
        'blue' => ['sent'],
    ];

    $statusClasses = [
        'gray' => 'text-gray-800 dark:text-gray-300',
        'orange' => 'text-orange-400 dark:text-orange-300',
        'red' => 'text-(--color-accent-2) dark:text-red-300',
        'green' => 'text-green-800 dark:text-green-300',
        'blue' => 'text-(--color-accent) dark:text-blue-300',
    ];

    $badgeClasses = [
        'gray' => 'bg-gray-100 dark:bg-gray-700',
        'orange' => 'bg-orange-100 dark:bg-orange-900',
        'red' => 'bg-(--color-accent-2-muted) dark:bg-red-900',
        'green' => 'bg-green-100 dark:bg-green-900',
        'blue' => 'bg-(--color-accent-muted) dark:bg-blue-900',
    ];

    $group = collect($statusGroups)
        ->filter(fn($statuses) => in_array($status, $statuses))
        ->keys()
        ->first() ?? 'gray';

    $textClass = $statusClasses[$group] ?? 'text-gray-800 dark:text-gray-300';
    $badgeClass = $badgeClasses[$group] ?? 'bg-gray-100 dark:bg-gray-700';
@endphp

<div class="flex flex-col items-center gap-1">
    <span class="text-md font-bold {{ $textClass }}">
        {{ $date }}
    </span>
    <span class="inline-block whitespace-nowrap px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClass }} {{ $textClass }}">
        {{ ucfirst($status) }}
    </span>
</div>