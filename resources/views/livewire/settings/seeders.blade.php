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
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Seeder Name') }}
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Status') }}
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Action') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($seeders as $seeder)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $seeder['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $seeder['status'] === 'completed' ? 'bg-green-100 text-green-800' :
                                   ($seeder['status'] === 'failed' ? 'bg-red-100 text-red-800' :
                                   'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($seeder['status']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="confirm('Are you sure you want to run this seeder?') || event.stopImmediatePropagation()"
                                    wire:click="runSeeder('{{ $seeder['name'] }}')"
                                    class="text-indigo-600 hover:text-indigo-900"
                                    {{ $seeder['status'] === 'completed' ? 'disabled' : '' }}>
                                {{ __('Run Seeder') }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-settings.layout>
</div>
