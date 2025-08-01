@if ($currentStep === 3)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Additional Details
        </h2>
   
       <div class="mt-3">
            <flux:input id="stock_location"
                :label="__('Stock Location')"
                type="text"
                value="Company Warehouse"
                class="dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600 w-full rounded"
                readonly />
            <small class="text-gray-500 dark:text-gray-400">
                Stock will be stored in the company’s warehouse.
            </small>
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
            <flux:button wire:click="save" variant="primary" color="blue">
                Save
            </flux:button>
        </div>
    </div>
@endif