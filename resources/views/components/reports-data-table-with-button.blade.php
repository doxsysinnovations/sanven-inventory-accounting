@props([
    'styleAttributes' => '',
    'title' => '',
    'description' => '',
    'headers' => [],
    'rows' => [],
    'rowColors' => [],
    'headerBackgroundColor' => 'bg-transparent',
    'emptyMessage' => 'No data available.',
    'buttonLabel' => 'Select All',
    'route' => ''
])

<div class="bg-white rounded-md dark:bg-zinc-900 shadow {{ $styleAttributes }}">
     <div class="mb-4 p-4 bg-gray-50 rounded-t-md">
        <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-gray-100">
               {{ $title }}
        </h3>
        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>

    <div class="overflow-auto rounded-sm dark:border-zinc-700 px-6">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="{{ $headerBackgroundColor }} text-black text-left">
                <tr>
                    @foreach ($headers as $header)
                        <th class="text-sm px-4 py-2 font-semibold whitespace-nowrap border-b-1">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
           <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-sm">
                @if (count($rows) === 0)
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @else
                    @foreach ($rows as $rowIndex => $row)
                        <tr class="border-b-1 border-border-zinc-700">
                            @foreach ($row as $colIndex => $cell)
                                @php
                                    $textClass = $rowColors[$rowIndex][$colIndex] ?? null;
                                    $hasBadge = $textClass !== null;
                                @endphp
                                    <td class="py-2 px-4">
                                        @if ($hasBadge)
                                            <span class="inline-block whitespace-nowrap px-2 py-0.5 text-sm sm:text-base font-semibold rounded-full">
                                                {{ $cell }}
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">
                                                {{ $cell }}
                                            </span>
                                        @endif
                                    </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="mt-6 text-right pr-6">
        <div class="flex justify-end mb-6 md:mb-0">
            <a href="{{ route($route) }}">
                <flux:button variant="primary" color="blue">{{ $buttonLabel }}</flux:button>                                
            </a>
        </div>
    </div>
</div>