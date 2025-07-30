@props([
    'title' => 'Delete',
    'message' => 'Are you sure you want to delete this quotation? This action cannot be undone.',
    'onDelete' => 'delete',
    'onCancel' =>  '',
])

<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
        </div>
        <div
            class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
            <div class="bg-white dark:bg-(--color-accent-dark) px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-(--color-accent-4-dark) gap-2 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <flux:button variant="danger" wire:click="{{ $onDelete }}">Delete</flux:button>
                <flux:button wire:click="{{ $onCancel }}">Cancel</flux:button>
            </div>
        </div>
    </div>
</div>