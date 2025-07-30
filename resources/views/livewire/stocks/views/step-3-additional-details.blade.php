@if ($currentStep === 3)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Additional Details
        </h2>
   
        <div>
            <flux:select :label="__('Stock Location')" id="stock_location" wire:model="stock_location" placeholder="Select a location"
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded">
                 <flux:select.option value="">Choose Location...</flux:select.option>
                @foreach ($locations as $location)
                    <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <small class="text-gray-500 dark:text-gray-400">Select where the stock will be stored.</small>
        </div>

        <div class="mt-3">
            <flux:textarea :label="__('Batch Notes')" id="batch_notes" wire:model="batch_notes"
                placeholder="Enter any notes about the batch"
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded">
            </flux:textarea>
            <small class="text-gray-500 dark:text-gray-400">Add any additional notes or instructions for this batch.</small>
        </div>
        
        <div class="flex justify-between mt-4 overflow-hidden">
            <flux:button variant="ghost" color="zinc" wire:click="previousStep">
                Back
            </flux:button>
            <flux:button wire:submit.prevent="save" variant="primary" color="blue" wire:click="nextStep">
                Save
            </flux:button>
        </div>
    </div>
@endif