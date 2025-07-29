<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use App\Models\AgentCommission;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $supplier;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $supplierToDelete;
    public $name = '';
    public $contact_number = '';
    public $address = '';
    public $email = '';
    public $trade_name = '';
    public $identification_number = '';

    public $infoModal = false;
    public $supplierInfo;
    public $activeTab = 'basic';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'trade_name' => 'required|string|max:255',
            'identification_number' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => $this->isEditing ? 'nullable|email|unique:suppliers,email,' . $this->supplier->id : 'nullable|email|unique:suppliers,email',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Supplier $supplier)
    {
        $this->infoModal = false;
        $this->resetValidation();
        $this->supplier = $supplier;
        $this->name = $supplier->name;
        $this->trade_name = $supplier->trade_name;
        $this->identification_number = $supplier->identification_number;
        $this->contact_number = $supplier->contact_number;
        $this->address = $supplier->address;
        $this->email = $supplier->email;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->supplier->update([
                'name' => $this->name,
                'trade_name' => $this->trade_name,
                'identification_number' => $this->identification_number,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'email' => $this->email,
            ]);
            flash()->success('Supplier updated successfully!');
        } else {
            Supplier::create([
                'name' => $this->name,
                'trade_name' => $this->trade_name,
                'identification_number' => $this->identification_number,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'email' => $this->email,
            ]);
            flash()->success('Supplier created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($supplierId)
    {
        $this->supplierToDelete = $supplierId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $supplier = Supplier::find($this->supplierToDelete);
        if ($supplier) {
            $supplier->delete();
            flash()->success('Supplier deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->supplierToDelete = null;
    }

    private function resetForm()
    {
        $this->name = '';
        $this->trade_name = '';
        $this->identification_number = '';
        $this->contact_number = '';
        $this->address = '';
        $this->email = '';
        $this->supplier = null;
        $this->resetValidation();
    }

    #[Title('Suppliers')]
    public function with(): array
    {
        return [
            'suppliers' => $this->suppliers,
            'agentCommissions' => $this->agentCommissions,
        ];
    }

    public function info($supplierId)
    {
        $this->supplierInfo = Supplier::find($supplierId);
        $this->activeTab = 'basic';
        $this->infoModal = true;
    }

    public function getSuppliersProperty()
    {
        return Supplier::query()
            ->where('identification_number', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orWhere('trade_name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);
    }

    public function getAgentCommissionsProperty()
    {
        return AgentCommission::query()
            ->when($this->search, function ($query) {
                $query->where('agent_name', 'like', '%' . $this->search . '%')->orWhere('invoice_number', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);
    }

    public function approveCommission($id)
    {
        $commission = AgentCommission::find($id);
        if ($commission && $commission->status === 'pending') {
            $commission->status = 'approved';
            $commission->save();
            session()->flash('message', 'Commission approved.');
        }
    }

    public function declineCommission($id)
    {
        $commission = AgentCommission::find($id);
        if ($commission && $commission->status === 'pending') {
            $commission->status = 'declined';
            $commission->save();
            session()->flash('message', 'Commission declined.');
        }
    }

    public function cancelCommission($id)
    {
        $commission = AgentCommission::find($id);
        if ($commission && $commission->status === 'pending') {
            $commission->status = 'cancelled';
            $commission->save();
            session()->flash('message', 'Commission cancelled.');
        }
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
                        <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">Commissions</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h2>
            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agents' Commisions</span>
            <p>
                <span class="text-sm text-gray-500 dark:text-gray-400">Track your agents' commissions</span>
            </p>
        </h2>
        <div class="flex items-center justify-between">
            <div class="w-1/3">
                <input wire:model.live="search" type="search" placeholder="Search agents..."
                    class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 focus:outline-none transition duration-200 dark:border-gray-600">
            </div>
        </div>
        @if ($suppliers->isEmpty())
            <div class="flex flex-col items-center justify-center p-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-48 h-48 mb-4 text-gray-300 dark:text-gray-600"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <p class="mb-4 text-gray-500 dark:text-gray-400">No agents' commissions' found</p>

            </div>
        @else
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Agent Name</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Invoice #</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Commission</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Status</th>
                            {{-- <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Address</th> --}}
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($agentCommissions as $commission)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ $commission->agent->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ $commission->invoice->invoice_number }}</td>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ $commission->commission_amount }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span @class([
                                        'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' =>
                                            $commission->status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' =>
                                            $commission->status === 'approved',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' =>
                                            $commission->status === 'declined',
                                        'bg-gray-50 text-gray-800 dark:bg-gray-700 dark:text-gray-100' =>
                                            $commission->status === 'cancelled',
                                    ])>
                                        {{ ucfirst($commission->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($commission->created_at ?? '')->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 flex gap-2">
                                    @if ($commission->status === 'pending')
                                        <div x-data="{ tooltip: false }" class="relative" @mouseenter="tooltip = true"
                                            @mouseleave="tooltip = false">
                                            <span x-cloak x-show="tooltip" x-transition
                                                class="absolute z-10 px-2 py-1 text-xs text-white bg-emerald-600 rounded shadow-lg left-1/2 -translate-x-1/2 bottom-full mb-2"
                                                style="white-space: nowrap;">
                                                Approve
                                            </span>
                                            <button wire:click="approveCommission({{ $commission->id }})"
                                                class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-500 hover:bg-emerald-600 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
                                                aria-label="Approve">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-data="{ tooltip: false }" class="relative" @mouseenter="tooltip = true"
                                            @mouseleave="tooltip = false">
                                            <span x-cloak x-show="tooltip" x-transition
                                                class="absolute z-10 px-2 py-1 text-xs text-white bg-rose-600 rounded shadow-lg left-1/2 -translate-x-1/2 bottom-full mb-2"
                                                style="white-space: nowrap;">
                                                Decline
                                            </span>
                                            <button wire:click="declineCommission({{ $commission->id }})"
                                                class="inline-flex items-center px-2 py-1 rounded-md bg-rose-500 hover:bg-rose-600 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-rose-400 cursor-pointer"
                                                aria-label="Decline">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-data="{ tooltip: false }" class="relative" @mouseenter="tooltip = true"
                                            @mouseleave="tooltip = false">
                                            <span x-cloak x-show="tooltip" x-transition
                                                class="absolute z-10 px-2 py-1 text-xs text-white bg-slate-500 rounded shadow-lg left-1/2 -translate-x-1/2 bottom-full mb-2"
                                                style="white-space: nowrap;">
                                                Cancel
                                            </span>
                                            <button wire:click="cancelCommission({{ $commission->id }})"
                                                class="inline-flex items-center px-2 py-1 rounded-md bg-slate-400 hover:bg-slate-500 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300 cursor-pointer"
                                                aria-label="Cancel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8 12h8" />
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No actions</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $agentCommissions->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

                            <div class="mb-4">
                                <flux:input wire:model="name" :label="__('Name')" type="text"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="trade_name" :label="__('Trade Name')" type="text"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="identification_number" :label="__('Identification Number')"
                                    type="text" class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="contact_number" :label="__('Contact Number')" type="text"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                            </div>
                            <div class="mb-4">
                                <flux:input wire:model="email" :label="__('Email')" type="email"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                            </div>
                            <div class="mb-4">
                                <flux:textarea wire:model="address" :label="__('Address')"
                                    class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600" />
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <flux:button type="submit" class="sm:ml-3 sm:w-auto sm:text-sm" variant="primary">
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </flux:button>
                            <button type="button" wire:click="$set('showModal', false)"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($infoModal && $supplierInfo)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-800 bg-opacity-75 transition-opacity"
                    aria-hidden="true"></div>

                <!-- Modal content -->
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:align-middle">
                    <!-- Banner -->
                    <div
                        class="relative h-32 w-full bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-700 dark:to-blue-800">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <h3 class="text-xl font-bold text-white dark:text-gray-100">Supplier Profile</h3>
                        </div>
                    </div>

                    <!-- Profile section -->
                    <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex">
                            <!-- Profile Picture -->
                            <div class="relative -mt-16 mr-6">
                                <div
                                    class="h-32 w-32 rounded-full border-4 border-white dark:border-gray-800 bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                    <svg class="h-full w-full text-gray-400 dark:text-gray-500" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Supplier name -->
                            <div class="mt-2">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $supplierInfo->name }}
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400">ID:
                                    {{ $supplierInfo->identification_number }}</p>
                                <p class="text-gray-500 dark:text-gray-400">{{ $supplierInfo->trade_name }}</p>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="mt-6 border-b border-gray-200 dark:border-gray-700">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button wire:click="$set('activeTab', 'basic')"
                                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $activeTab === 'basic' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                    Basic Info
                                </button>
                                <button wire:click="$set('activeTab', 'contact')"
                                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $activeTab === 'contact' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                    Contact Details
                                </button>
                            </nav>
                        </div>

                        <!-- Tab content -->
                        <div class="mt-4">
                            @if ($activeTab === 'basic')
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full
                                            Name</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trade
                                            Name</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->trade_name }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Identification
                                            Number</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->identification_number }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created
                                            At</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->created_at?->format('M d, Y h:i A') ?? 'Not provided' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email
                                            Address</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->email ?? 'Not provided' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact
                                            Number</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->contact_number ?? 'Not provided' }}
                                        </p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $supplierInfo->address ?? 'Not provided' }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="$set('infoModal', false)"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-400 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                        @can('suppliers.edit')
                            <button wire:click="edit({{ $supplierInfo->id }})" type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Edit Supplier
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-800 opacity-75"></div>
                </div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Delete Supplier
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Are you sure you want to delete this supplier? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="delete"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-400 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="$set('confirmingDelete', false)"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
