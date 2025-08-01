<?php

use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use setasign\Fpdi\Fpdi;

new class extends Component {
    use WithFileUploads;

    public $files = [];
    public $mergedFileName = 'merged.pdf';
    public $status = '';

    public function merge()
    {
        $this->validate([
            'files.*' => 'required|mimes:pdf|max:10240',
            'mergedFileName' => 'required|string'
        ]);

        try {
            // Make sure FPDI package is installed via composer:
            // composer require setasign/fpdi
            $merger = new Fpdi();

            foreach ($this->files as $file) {
                $pageCount = $merger->setSourceFile($file->getPathname());
                for ($i = 1; $i <= $pageCount; $i++) {
                    $template = $merger->importPage($i);
                    $size = $merger->getTemplateSize($template);
                    $merger->AddPage($size['orientation'], array($size['width'], $size['height']));
                    $merger->useTemplate($template);
                }
            }

            $outputPath = storage_path('app/public/' . $this->mergedFileName);
            $merger->Output($outputPath, 'F');

            $this->status = 'PDF files merged successfully!';
            $this->files = [];

            return response()->download($outputPath)->deleteFileAfterSend();

        } catch (\Exception $e) {
            $this->status = 'Error: ' . $e->getMessage();
        }
    }
}; ?>

<div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-6 flex flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <div class="relative px-4 py-10 bg-white dark:bg-gray-800 mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
            <div class="max-w-md mx-auto">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="py-8 text-base leading-6 space-y-4 text-gray-700 dark:text-gray-300 sm:text-lg sm:leading-7">
                        <h2 class="text-2xl font-bold mb-8 text-center text-gray-800 dark:text-white">PDF Merger</h2>

                        <form wire:submit.prevent="merge" class="space-y-4">
                            <div class="flex flex-col">
                                <label class="leading-loose dark:text-gray-300">Output Filename</label>
                                <input type="text" wire:model="mergedFileName"
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600"
                                    placeholder="merged.pdf">
                            </div>

                            <div class="flex flex-col">
                                <label class="leading-loose dark:text-gray-300">Select PDF Files</label>
                                <input type="file" wire:model="files" multiple accept=".pdf"
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600">
                            </div>

                            <div wire:loading wire:target="files" class="text-sm text-gray-500 dark:text-gray-400">
                                Uploading files...
                            </div>

                            @if($status)
                                <div class="text-sm" :class="{'text-green-500': !status.includes('Error'), 'text-red-500': status.includes('Error')}">
                                    {{ $status }}
                                </div>
                            @endif

                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed">
                                Merge PDFs
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
