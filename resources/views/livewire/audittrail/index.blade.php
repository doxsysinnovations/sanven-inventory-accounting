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
    public $perPage = 5;

    public function mount()
    {
        $this->perPage = session('perPage', 5);
    }

    public function updatedPerPage($value)
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

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
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="All Activities"
        :items="$activities"
        :withSearch="false"
        :withDateFilter="true"
        message="No activities."
        :perPage="$perPage"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a28.076 28.076 0 0 1-1.091 9M7.231 4.37a8.994 8.994 0 0 1 12.88 3.73M2.958 15S3 14.577 3 12a8.949 8.949 0 0 1 1.735-5.307m12.84 3.088A5.98 5.98 0 0 1 18 12a30 30 0 0 1-.464 6.232M6 12a6 6 0 0 1 9.352-4.974M4 21a5.964 5.964 0 0 1 1.01-3.328 5.15 5.15 0 0 0 .786-1.926m8.66 2.486a13.96 13.96 0 0 1-.962 2.683M7.5 19.336C9 17.092 9 14.845 9 12a3 3 0 1 1 6 0c0 .749 0 1.521-.031 2.311M12 12c0 3 0 6-2 9"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Model', 'Event', 'Changed By', 'Created By', 'Updated On', 'Created At']"
            :rows="$activities->map(fn($activity) => [
                optional($activity->subject)->name ?? 'N/A',
                class_basename(optional($activity)->subject_type) ?? 'N/A',
                optional($activity)->description ?? 'N/A',
                optional($activity->causer)->name ?? 'System',
                optional($activity->causer)->name ?? 'System',
                optional(optional($activity->subject)->updated_at)?->format('Y-m-d H:i:s') ?? 'N/A',
                optional($activity->created_at)?->format('Y-m-d H:i:s') ?? 'N/A',
                '__model' => $activity,
            ])"
        />
    </x-view-layout>
</div>