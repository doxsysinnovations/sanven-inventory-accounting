@props([
    'styleAttributes' => '',
    'title' => '',
    'description' => '',
    'headers' => [],
    'rows' => [],
    'rowColors' => [],
    'headerBackgroundColor' => 'bg-transparent',
    'emptyMessage' => 'No data available.',
    'buttonLabel' => 'Select All'
])

<div class="bg-white rounded-xl dark:bg-zinc-900 {{ $styleAttributes }}">
     <div class="mb-4 p-4 bg-gray-50 rounded-t-md">
        <h2 class="text-xl font-bold  text-(--color-accent) dark:text-white">{{ $title }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>

    <div class="overflow-auto rounded-sm dark:border-zinc-700 px-6 pb-6">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="{{ $headerBackgroundColor }} text-black text-left text-sm">
                <tr>
                    @foreach ($headers as $header)
                        <th class="px-4 py-2 font-semibold whitespace-nowrap border-b-1">{{ $header }}</th>
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
                                        <span class="inline-block {{ $textClass }} rounded-3xl">
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
    
    <div class="mt-6 text-right pr-6 pb-3">
        <a href="{{ route('agingreports') }}"
           class="inline-flex items-center font-medium px-4 py-2 bg-(--color-accent) text-white rounded hover:bg-(--color-accent-alt) dark:bg-indigo-500 dark:hover:bg-indigo-600">
            {{ $buttonLabel }}
        </a>
    </div>
</div>