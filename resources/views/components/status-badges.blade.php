@props([
    'date' => null,
    'status',
])

@php
    $statusGroups = [
        'gray' => ['valid'],
        'orange' => ['expiring', 'Agent', 'cancelled'],
        'yellow' => ['pending'],
        'red' => ['expired', 'inactive', 'Admin', 'overdue'],
        'green' => ['accepted', 'active', 'Staff', 'paid'],
        'blue' => ['sent', 'Super Admin'],
    ];

    $statusClasses = [
        'gray' => 'text-gray-800 dark:text-gray-300',
        'orange' => 'text-orange-400 dark:text-orange-300',
        'yellow' => 'text-(--color-yellow-400) dark:text-yellow-300)',
        'red' => 'text-(--color-accent-2) dark:text-red-300',
        'green' => 'text-green-800 dark:text-green-300',
        'blue' => 'text-(--color-accent) dark:text-blue-300',
    ];

    $badgeClasses = [
        'gray' => 'bg-gray-100 dark:bg-gray-700',
        'orange' => 'bg-orange-100 dark:bg-orange-900',
        'yellow' => 'bg-(--color-yellow-100) dark:bg-yellow-900',
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

<div class="flex">
    <div class="flex flex-col items-center">
        <div>
            <span class="text-sm font-bold {{ $textClass }}">
                {{ $date }}
            </span>
        </div>
        <div>
            <span class="inline-block whitespace-nowrap px-3 py-0.5 text-sm font-semibold rounded-full {{ $badgeClass }} {{ $textClass }}">
                {{ ucfirst($status) }}
             </span>
        </div>
    </div>
</div>