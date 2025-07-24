@props([
    'headers' => [],
    'rows' => [],
    'emptyMessage' => 'No data available.',
    'actions' => null,
    'keyPrefix' => 'row',
    'viewAbility' => '',
    'viewRoute' => '',
    'editAbility' => '',
    'editRoute' => '',
    'deleteAbility' => '',
    'deleteAction' => '',
    'editParameter' => '$id'
])

<div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
            @forelse ($rows as $row)
                @php
                    $model = $row['__model'] ?? null;
                    unset($row['__model']);
                @endphp
                <tr class="dark:hover:bg-gray-800" wire:key="{{ $keyPrefix }}-{{ $model?->id ?? uniqid() }}">
                    @foreach ($headers as $i => $header)
                        @php
                            $cell = $row[$i] ?? null;
                        @endphp
                        <td class="text-left px-6 py-4 dark:text-gray-300">
                            @if (strtolower($header) === 'status' || strtolower($header) === 'expiry date' || strtolower($header) === 'roles' || strtolower($header) === 'user type')
                               @if (is_array($cell) && isset($cell['date'], $cell['status']))
                                    <x-status-badges :date="$cell['date']" :status="$cell['status']" />
                                @else
                                    <x-status-badges :status="$cell" />
                                @endif
                            @elseif (strtolower($header) === 'actions')
                                @if ($model)
                                    <x-actions 
                                        :model="$model"
                                        :viewAbility="$viewAbility"
                                        :viewRoute="$viewRoute"
                                        :editAbility="$editAbility"
                                        :editRouteParameter="$editParameter"
                                        :editRoute="$editRoute"
                                        :deleteAbility="$deleteAbility"
                                        :deleteAction="$deleteAction"
                                    />
                                @endif
                            @else
                                {!! $cell !!}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-6 py-4 text-left text-gray-400">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>