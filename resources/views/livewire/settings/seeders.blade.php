<?php

use Livewire\Volt\Component;

new class extends Component {
    public $seeders = [];

    public function mount()
    {
        // Get all seeder classes from database/seeders directory
        $this->seeders = collect(File::files(database_path('seeders')))
            ->map(function ($file) {
                return [
                    'name' => str_replace('.php', '', $file->getFilename()),
                    'status' => 'pending'
                ];
            })->toArray();
    }

    public function runSeeder($seederName)
    {
        try {
            Artisan::call('db:seed', [
                '--class' => $seederName
            ]);

            $this->seeders = collect($this->seeders)->map(function($seeder) use ($seederName) {
                if ($seeder['name'] === $seederName) {
                    $seeder['status'] = 'completed';
                }
                return $seeder;
            })->toArray();
        } catch (\Exception $e) {
            $this->seeders = collect($this->seeders)->map(function($seeder) use ($seederName) {
                if ($seeder['name'] === $seederName) {
                    $seeder['status'] = 'failed';
                }
                return $seeder;
            })->toArray();
        }
    }
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Seeders')" :subheading="__('Run Seeders to populate the database with initial data')">
        <div class="w-full">
          <x-list-table
                :headers="['Seeder Name', 'Status', 'Action']"
                :rows="collect($seeders)->map(fn($seeder) => [
                    $seeder['name'],
                    $seeder['status'],
                    view('livewire.settings.seeder-action', ['seeder' => $seeder])->render(),
                ])"
                emptyMessage="No seeders available."
            />
        </div>
    </x-settings.layout>
</div>
