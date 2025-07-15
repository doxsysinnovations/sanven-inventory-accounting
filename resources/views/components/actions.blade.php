@props([
    'model',
    'viewAbility' => null,
    'viewRoute' => null,
    'editAbility' => null,
    'editRoute' => null,
    'deleteAbility' => null,
    'deleteAction' => null,
])

@php
    $actionClasses = [
        'view' => 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200',
        'edit' => 'inline-flex items-center justify-center w-8 h-8 rounded-full transition-all
                text-(--color-accent) hover:text-white hover:bg-(--color-accent)
                focus:outline-none focus:ring-2 focus:ring-(--color-accent) focus:ring-offset-2 dark:focus:ring-offset-gray-900',
        'delete' => 'inline-flex items-center justify-center w-8 h-8 rounded-full transition-all
                text-(--color-accent-2) hover:text-white hover:bg-(--color-accent-2)
                focus:outline-none focus:ring-2 focus:ring-(--color-accent-2) focus:ring-offset-2 dark:focus:ring-offset-gray-900',
    ];
@endphp

<div class="flex items-center">
    @if ($viewAbility && $viewRoute)
        @can($viewAbility)
        <div>
            <a href="{{ route($viewRoute, $model->id) }}" class="{{ $actionClasses['view'] }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </a>
        </div>
        @endcan
    @endif

    @if ($editAbility && $editRoute)
        @can($editAbility)
        <div>
            <a href="{{ route($editRoute, $model->id) }}" class="{{ $actionClasses['edit'] }}">
               <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
        @endcan
    @endif

    @if ($deleteAbility && $deleteAction)
        @can($deleteAbility)
        <div>
            <button wire:click="{{ $deleteAction }}({{ $model->id }})" class="{{ $actionClasses['delete'] }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"  d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" wire:click clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endcan
    @endif
</div>