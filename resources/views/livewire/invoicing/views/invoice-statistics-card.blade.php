@props([
    'title' => '',
    'value' => '',
    'label' => '',
])

<div class="flex gap-3 items-center w-full cursor-pointer p-5 rounded-lg bg-gray-50 dark:bg-(--color-accent-4-dark) hover:bg-gray-200 dark:hover:bg-gray-700">
    @isset($iconSlot)
        {{ $iconSlot }}
    @else
        <div class="h-14 w-14 min-w-14 rounded-full border-2 border-blue-500 text-blue-500 flex justify-center items-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                <path d="M13.358 21c2.227 0 3.341 0 4.27-.533c.93-.532 1.52-1.509 2.701-3.462l.681-1.126c.993-1.643 1.49-2.465 1.49-3.379s-.497-1.736-1.49-3.379l-.68-1.126c-1.181-1.953-1.771-2.93-2.701-3.462C16.699 4 15.585 4 13.358 4h-2.637C9.683 4 8.783 4 8 4.024m-4.296 1.22C2.5 6.49 2.5 8.495 2.5 12.5s0 6.01 1.204 7.255c.998 1.033 2.501 1.209 5.196 1.239M7.5 7.995V17">
                </path>
            </svg>
        </div>  
    @endisset

    <div class="flex-1 min-w-0">
        <h5 class="text-base font-medium truncate">{{ $title }}</h5>
        <p class="text-sm opacity-80 truncate">{{ $label }}</p>
        <h6 class="text-sm font-semibold truncate">₱{{ $value }}</h6>
    </div>
</div>
