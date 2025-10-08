@props([
    'title' => '',
    'description' => '',
    'headers' => [],
    'rows' => [],
    'rowColors' => [],
    'headerBackgroundColor' => 'bg-transparent',
    'emptyMessage' => 'No data available.',
    'buttonLabel' => 'See All',
    'route' => ''
])

@php
    $statusIndex = array_search('Status', $headers);
@endphp

<div class="bg-white rounded-lg shadow dark:bg-(--color-accent-dark) dark:text-white">
    <div class="mb-4 p-6 rounded-t-lg bg-gray-50 dark:bg-(--color-accent-4-dark) dark:text-white dark:rounded-t-md">
        <h3 class="font-bold text-lg lg:text-xl text-(--color-accent) dark:text-gray-100">
               {{ $title }}
        </h3>
        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>

    <div class="overflow-x-auto rounded-lg dark:border-zinc-700 px-10 pb-10">
        <table class="w-full table-auto divide-y divide-gray-200">
            <thead class="{{ $headerBackgroundColor }}  text-black dark:text-white text-left">
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
                        <tr class="border-b-1 dark:border-white">
                         @foreach ($row as $colIndex => $cell)
                                @php
                                    $statusIndex = array_search('Status', $headers);
                                    $dateIndex = array_search('Date', $headers);
                                    $isStatusCol = $colIndex === $statusIndex;
                                    $isDateCol = $colIndex === $dateIndex;
                                @endphp

                                <td class="py-2 px-4">
                                    @if ($isStatusCol)
                                        <x-status-badges
                                            :status="$cell"
                                        />
                                    @elseif (!empty($rowColors[$rowIndex][$colIndex]))
                                        <x-status-badges :status="$cell" />
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

    <div class="mt-6 text-right pr-10 pb-10">
        <div class="flex justify-end">
            <a href="{{ route($route) }}">
                <flux:button variant="primary" color="blue">{{ $buttonLabel }}</flux:button>                                
            </a>
        </div>
    </div>
</div>
