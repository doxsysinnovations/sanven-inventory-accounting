@props([
    'title' => '',
    'description' => '',
    'headers' => [],
    'rows' => [],
    'rowColors' => [],
    'headerBackgroundColor' => 'bg-transparent',
    'emptyMessage' => 'No data available.',
])

<div class="p-6 bg-white rounded-xl dark:bg-zinc-900">
    <div class="mb-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $title }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>

    <div class="overflow-auto rounded-sm dark:border-zinc-700">
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
                                        <span class="text-sm inline-block {{ $textClass }} rounded-3xl">
                                            {{ $cell }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
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
</div>
