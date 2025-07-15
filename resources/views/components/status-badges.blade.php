@props([
    'status' => ''
])

@php
    $statusGroups = [
        'gray' => ['draft'],
        'blue' => ['sent'],
        'green' => ['accepted'],
        'red' => ['rejected']
    ];

    $statusClasses = [
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    ];

    // Find the group key by status
    $group = collect($statusGroups)
        ->filter(fn($statuses) => in_array($status, $statuses))
        ->keys()
        ->first() ?? 'gray';

    $class = $statusClasses[$group];
@endphp

<span class="px-2 py-1 text-xs font-medium rounded-full {{ $class }}">
    {{ ucfirst($status) }}
</span>
