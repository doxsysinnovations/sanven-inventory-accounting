<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $customer;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $activeTab = 'basic';
    public $customerInfo;

    public  function mount(Customer $id) {
         $this->customerInfo = $id;
        $this->activeTab = 'basic';
    }

    public function with(): array
    {
        return [
            'customers' => $this->customers,
        ];
    }

    public function getCustomersProperty()
    {
        return Customer::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);
    }
};

?>

<div>
    <div class="bg-white rounded-lg dark:bg-gray-900 sm:pb-4">
         <div class="bg-gray-50 p-6 flex flex-col rounded-t-lg">
            <h3 class="text-xl font-bold text-[color:var(--color-accent)] dark:text-gray-100">
                Customer Profile
            </h3>
        </div>

        <div class="flex flex-col px-4 pt-5 pb-4 sm:p-6">
            <div class="flex justify-between">
                <div class="flex items-center gap-5">
                    <div>
                        <div class="h-35 w-35 rounded-full border-4 border-white dark:border-gray-800 bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                            <svg class="h-full w-full text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        
                    </div>
                    <div class="flex flex-col mt-2 gap-4">
                        <div>
                            <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $customerInfo->name }}
                            </h3>
                        </div>
                        <div class="flex gap-6">
                            <x-info-label 
                                label=" Email"
                                :value="$customerInfo->email"
                            />
                            <x-info-label 
                                label="Address"
                                :value="$customerInfo->address"
                            />
                            <x-info-label 
                                label="Phone Number"
                                :value="$customerInfo->phone ?? 'Not provided'"
                            />
                        </div>
                    </div>
                </div> 
                <div>
                    <a href="{{ route('customers.edit') }}">
                        <flux:button variant="primary" icon="pencil">Edit Profile</flux:button>                                
                    </a>
                </div>
            </div>
            <div>
                <div class="">
                    <div class="mt-6 border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button wire:click="$set('activeTab', 'basic')"
                                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold {{ $activeTab === 'basic' ? 'border-(--color-accent) text-(--color-accent) dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Basic Info
                            </button>
                            <button wire:click="$set('activeTab', 'history')"
                                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold {{ $activeTab === 'history' ? 'border-(--color-accent) text--(--color-accent) dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                History
                            </button>
                        </nav>
                    </div>

                    <div class="mt-4">
                        @if($activeTab === 'basic')
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                               <x-info-label 
                                    label="Cutomer Name"
                                    :value="$customerInfo->name"
                                />
                                <x-info-label 
                                    label="Created At"
                                    :value="$customerInfo->created_at->format('M d, Y h:i A')"
                                />
                                <x-info-label 
                                    label=" Customer ID"
                                    :value="$customerInfo->id"
                                />
                                <x-info-label 
                                    label="Last Updated"
                                    :value="$customerInfo->created_at->format('M d, Y h:i A')"
                                />
                            </div>
                        @else
                            <div class="rounded-md bg-gray-50 dark:bg-gray-800 p-4">
                                <div class="text-center">
                                    <svg class="mx-auto h-20 w-20 text-gray-400 dark:text-gray-500"  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10.827 5.465-.435-2.324m.435 2.324a5.338 5.338 0 0 1 6.033 4.333l.331 1.769c.44 2.345 2.383 2.588 2.6 3.761.11.586.22 1.171-.31 1.271l-12.7 2.377c-.529.099-.639-.488-.749-1.074C5.813 16.73 7.538 15.8 7.1 13.455c-.219-1.169.218 1.162-.33-1.769a5.338 5.338 0 0 1 4.058-6.221Zm-7.046 4.41c.143-1.877.822-3.461 2.086-4.856m2.646 13.633a3.472 3.472 0 0 0 6.728-.777l.09-.5-6.818 1.277Z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No activity yet.</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customer history will appear here when available.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>