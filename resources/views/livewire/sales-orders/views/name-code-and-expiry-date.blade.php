<div class="font-medium text-gray-900 dark:text-gray-100">
        {{ $item['name'] }}
</div>
<div class="text-xs text-gray-500 dark:text-gray-400">
    {{ $item['code'] }}
    {{-- @if ($item['expiration_date'])
        | Exp: {{ $item['expiration_date'] }}
    @endif --}}
    @if ($item['batch_number'])
        | Batch: {{ $item['batch_number'] }}
    @endif
</div>