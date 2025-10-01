<button type="button" wire:click="removeFromCart('{{ $key }}')"
    class="visible inline-flex items-center justify-center w-8 h-8 rounded-full text-red-600 hover:text-white hover:bg-red-600 transition-all focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
        viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
            clip-rule="evenodd" />
    </svg>
</button>