@props([
    'title',
    'description' => null,
    'headers' => [],
    'rows' => [],
    'headerBackgroundColor' => 'bg-[#D86B59]',      
    'evenBackgroundColor' => 'bg-rgba(216,107,89,0.1)', 
    'rowColors' => [],
])


<div class="relative overflow-hidden shadow rounded-xl border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-4">
    <div class="mb-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
        @if ($description)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $description }}</p>
        @endif

    </div>
    <table class="w-full text-sm text-left border-collapse">
        <thead class="text-white uppercase {{ $headerBackgroundColor }}">
            <tr>
                @foreach ($headers as $head)
                    <th class="py-3 px-4 font-semibold">{{ $head }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
               <tr class="odd:bg-white {{ $loop->even ? $evenBackgroundColor : '' }} dark:odd:bg-gray-900 dark:even:bg-gray-800 border-gray-200 dark:border-gray-700">
                   @foreach ($row as $index => $cell)
                        @php
                            $textClass = $rowColors[$loop->parent->index][$index] ?? 'text-gray-700 dark:text-gray-300';
                    @endphp
                        <td class="py-2 px-4 {{ $textClass }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="py-2 px-4 text-gray-500 dark:text-gray-400">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
