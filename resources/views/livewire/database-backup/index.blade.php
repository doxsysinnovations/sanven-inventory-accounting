<?php

use Livewire\Volt\Component;
use App\Models\DatabaseBackupHistory;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Backup\BackupJob;

new class extends Component {
    public $creatingBackup = false;
    public $message = '';
    public $messageType = ''; // success, error
    public $backupHistory = [];

    public function mount()
    {
        $this->loadBackupHistory();
    }

    public function loadBackupHistory()
    {
        $this->backupHistory = DatabaseBackupHistory::orderBy('created_at', 'desc')->get();
    }

    public function createBackup()
    {
        $this->creatingBackup = true;
        $this->message = '';
        $this->messageType = '';

        try {
            // Create backup using Spatie (new approach)
            $backupJob = new BackupJob();

            // Set backup options
            $backupJob->setFileExtension(config('backup.backup.destination.filename_prefix') . '.zip')->setBackupName(config('backup.backup.name'));

            // Run the backup
            $backupJob->run();

            // Get the latest backup file
            $backupDestination = BackupDestination::create(config('filesystems.default'), config('backup.backup.name'));
            $backupFiles = $backupDestination->backups();
            $latestBackup = $backupFiles->first();

            if ($latestBackup) {
                // Record in history
                $fileSize = $latestBackup->sizeInBytes() / (1024 * 1024); // Convert to MB

                DatabaseBackupHistory::create([
                    'file_name' => $latestBackup->path(),
                    'disk' => config('filesystems.default'),
                    'file_size' => round($fileSize, 2),
                    'status' => 'success',
                    'backup_date' => now(),
                ]);

                $this->message = 'Backup created successfully! File size: ' . round($fileSize, 2) . ' MB';
                $this->messageType = 'success';
            } else {
                $this->message = 'Backup created but file not found.';
                $this->messageType = 'error';
            }
        } catch (\Exception $e) {
            // Record failed backup
            DatabaseBackupHistory::create([
                'file_name' => 'failed_backup_' . now()->format('Y-m-d_H-i-s'),
                'disk' => config('filesystems.default'),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'backup_date' => now(),
            ]);

            $this->message = 'Backup failed: ' . $e->getMessage();
            $this->messageType = 'error';
        } finally {
            $this->creatingBackup = false;
            $this->loadBackupHistory();
        }
    }

    // Alternative method using Artisan command (more reliable)
    public function createBackupUsingArtisan()
    {
        $this->creatingBackup = true;
        $this->message = '';
        $this->messageType = '';

        try {
            // Execute backup via Artisan command
            \Artisan::call('backup:run');

            $output = \Artisan::output();

            // Get the latest backup file after running the command
            $backupDestination = BackupDestination::create(config('filesystems.default'), config('backup.backup.name'));
            $backupFiles = $backupDestination->backups();
            $latestBackup = $backupFiles->first();

            if ($latestBackup) {
                // Record in history
                $fileSize = $latestBackup->sizeInBytes() / (1024 * 1024); // Convert to MB

                DatabaseBackupHistory::create([
                    'file_name' => $latestBackup->path(),
                    'disk' => config('filesystems.default'),
                    'file_size' => round($fileSize, 2),
                    'status' => 'success',
                    'backup_date' => now(),
                ]);

                $this->message = 'Backup created successfully! File size: ' . round($fileSize, 2) . ' MB';
                $this->messageType = 'success';
            } else {
                $this->message = 'Backup command executed but no backup file was created.';
                $this->messageType = 'error';
            }
        } catch (\Exception $e) {
            // Record failed backup
            DatabaseBackupHistory::create([
                'file_name' => 'failed_backup_' . now()->format('Y-m-d_H-i-s'),
                'disk' => config('filesystems.default'),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'backup_date' => now(),
            ]);

            $this->message = 'Backup failed: ' . $e->getMessage();
            $this->messageType = 'error';
        } finally {
            $this->creatingBackup = false;
            $this->loadBackupHistory();
        }
    }

    public function deleteBackup($backupId)
    {
        // Remove the confirm check here - we'll handle it in the frontend
        try {
            $backupHistory = DatabaseBackupHistory::findOrFail($backupId);

            // Delete from storage
            if (Storage::disk($backupHistory->disk)->exists($backupHistory->file_name)) {
                Storage::disk($backupHistory->disk)->delete($backupHistory->file_name);
            }

            // Delete from history
            $backupHistory->delete();

            $this->message = 'Backup deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Delete failed: ' . $e->getMessage();
            $this->messageType = 'error';
        }

        $this->loadBackupHistory();
    }

    public function downloadBackup($backupId)
    {
        try {
            // Increase memory limit for large files
            ini_set('memory_limit', '512M');

            $backupHistory = DatabaseBackupHistory::findOrFail($backupId);

            if (!Storage::disk($backupHistory->disk)->exists($backupHistory->file_name)) {
                $this->message = 'Backup file not found.';
                $this->messageType = 'error';
                return;
            }

            return Storage::disk($backupHistory->disk)->download($backupHistory->file_name);
        } catch (\Exception $e) {
            $this->message = 'Download failed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function getTotalBackupsSizeProperty()
    {
        return $this->backupHistory->where('status', 'success')->sum('file_size');
    }

    public function getSuccessfulBackupsCountProperty()
    {
        return $this->backupHistory->where('status', 'success')->count();
    }

    public function getFailedBackupsCountProperty()
    {
        return $this->backupHistory->where('status', 'failed')->count();
    }
}; ?>

<div>
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Database Backup Manager</h2>
            <p class="text-gray-600 dark:text-gray-400">Manage your database backups and view backup history</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Backups</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ count($backupHistory) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-800 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Successful</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                            {{ $this->successfulBackupsCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center">
                    <div class="p-2 bg-gray-100 dark:bg-gray-600 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Size</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($this->totalBackupsSize, 2) }} MB
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Alert -->
        @if ($message)
            <div
                class="mb-4 p-4 rounded {{ $messageType === 'success' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800' }}">
                <div class="flex items-center">
                    @if ($messageType === 'success')
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                    {{ $message }}
                </div>
            </div>
        @endif

        <!-- Backup Controls -->
        <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Create New Backup</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Create a complete backup of your database and
                        files</p>
                </div>
                <div class="flex space-x-3">
                    <button x-data="{}"
                        @click="if(confirm('Are you sure you want to create a new backup?')) { $wire.createBackupUsingArtisan() }"
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center min-w-[160px]">
                        <span wire:loading.remove class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                </path>
                            </svg>
                            Create Backup
                        </span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Backup History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Backup History</h3>
            </div>

            @if (count($backupHistory) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    File Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Size</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($backupHistory as $backup)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ basename($backup->file_name) }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $backup->disk }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $backup->backup_date->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if ($backup->file_size)
                                            {{ number_format($backup->file_size, 2) }} MB
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status === 'success' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                                            {{ ucfirst($backup->status) }}
                                        </span>
                                        @if ($backup->error_message)
                                            <div class="text-xs text-red-600 dark:text-red-400 mt-1 truncate max-w-xs"
                                                title="{{ $backup->error_message }}">
                                                {{ Str::limit($backup->error_message, 50) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            @if ($backup->status === 'success')
                                                <button wire:click="downloadBackup({{ $backup->id }})"
                                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-200"
                                                    title="Download Backup">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            @endif
                                            <button x-data="{}"
                                                @click="if(confirm('Are you sure you want to delete this backup?')) { $wire.deleteBackup({{ $backup->id }}) }"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200"
                                                title="Delete Backup">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No backups</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first
                        database backup.</p>
                </div>
            @endif
        </div>
    </div>
</div>
