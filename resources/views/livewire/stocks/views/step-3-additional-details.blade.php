@if ($currentStep === 3)
    <div>
         <div class="flex items-center justify-between mb-6">
            <div class="w-full justify-between bg-gray-50 dark:bg-(--color-accent-4-dark) p-6 flex items-center rounded-lg">
                <div>
                    <h1 class="font-bold text-lg md:text-xl text-(--color-accent) dark:text-white">
                        Step 3: Additional Details
                    </h1>
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        You may add additional details before saving.
                    </span>
                </div>
            </div>
        </div>
   
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
                placeholder="Enter any notes about the batch.">
            </flux:textarea>
            <small class="text-gray-500 dark:text-gray-400">Add any additional notes or instructions for this batch.</small>
        </div>
        
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 mt-8 border-t border-gray-200 dark:border-gray-700 md:px-6">
            <flux:button variant="ghost" color="zinc" wire:click="previousStep" icon="chevron-left">
                    Back to Stock Information
            </flux:button>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div class="text-sm text-center sm:text-left text-gray-500 dark:text-gray-400">
                    <span>Step 3 of 3 - Additional Details</span>
                </div>
                
                <flux:button wire:click="save" variant="primary" color="blue">
                    <span>Save</span>
                </flux:button>
            </div>
        </div>
    </div>
@endif