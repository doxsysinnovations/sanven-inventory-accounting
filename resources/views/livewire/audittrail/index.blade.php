<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Unit;
use Livewire\Attributes\Title;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $startDate = '';
    public $endDate = '';

    #[Title('Audit Trail')]
    public function with(): array
    {
        return [
            'activities' => $this->activities,
        ];
    }

    public function getActivitiesProperty()
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            // ->when($this->search, function ($query) {
            //     $query->whereHasMorph('subject', '*', function ($query) {
            //         $query->where('name', 'like', '%' . $this->search . '%');
            //     });
            // })
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->latest()
            ->paginate(10);
    }
};

?>

<div>
    <div class="mb-4">
        <nav class="flex justify-end" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Audit
                            Trail</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="w-1/3">
                {{-- <input wire:model.live="search" type="search" placeholder="Search..."
                    class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600"> --}}
            </div>
            <div class="flex gap-4">
                <x-flux::input wire:model.live="startDate" type="date" label="Start" placeholder="Start" />
                <x-flux::input wire:model.live="endDate" type="date" label="End" placeholder="End" />
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Name
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Model
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Event
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Changed By
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Created By
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Updated On
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Created At
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($activities as $activity)
                        <tr class="dark:hover:bg-gray-800">
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">{{ $activity->subject->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                {{ class_basename($activity->subject_type) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">{{ $activity->description }}</td>
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                {{ $activity->causer->name ?? 'System' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                {{ $activity->causer->name ?? 'System' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                {{ $activity->subject->updated_at->format('Y-m-d H:i:s') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                {{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    </div>
</div>
