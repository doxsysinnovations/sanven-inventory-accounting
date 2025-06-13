<?php

use App\Models\NotificationRecipient;
use Livewire\Volt\Component;

new class extends Component {
    public $email = '';
    public $recipients = [];
    public $confirmingDelete = false;
    public $recipientToDelete = null;

    public function mount()
    {
        $this->recipients = NotificationRecipient::whereNull('deleted_at')->get();
    }

    public function addRecipient()
    {
        $this->validate([
            'email' => 'required|email|unique:notification_recipients,email',
        ]);
        NotificationRecipient::create(['email' => $this->email]);
        $this->email = '';
        $this->recipients = NotificationRecipient::whereNull('deleted_at')->get();
    }

    public function deactivateRecipient($id)
    {
        NotificationRecipient::where('id', $id)->update(['is_active' => false]);
        $this->recipients = NotificationRecipient::whereNull('deleted_at')->get();
    }

    public function activateRecipient($id)
    {
        NotificationRecipient::where('id', $id)->update(['is_active' => true]);
        $this->recipients = NotificationRecipient::whereNull('deleted_at')->get();
    }

    public function confirmDelete($id)
    {
        $this->recipientToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function deleteRecipient()
    {
        NotificationRecipient::find($this->recipientToDelete)?->delete();
        $this->recipients = NotificationRecipient::all();
        $this->confirmingDelete = false;
        $this->recipientToDelete = null;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Admin Panel')" :subheading="__('Administrative Settings')">
        <form wire:submit.prevent="addRecipient" class="mt-6 space-y-6">
            <flux:input wire:model="email" :label="__(' Add Email recipients')" type="email" required />
            <flux:button type="submit" variant="primary">{{ __('Add Recipient') }}</flux:button>
        </form>

        <div class="mt-12">
            <h3 class="font-semibold mb-2">Current Recipients</h3>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Only Active emails will receive notifications.</p>
            <ul>
                @foreach ($recipients as $recipient)
                    <li class="flex items-center justify-between py-2 border-b">
                        <span>
                            {{ $recipient->email }}
                            @if(!$recipient->is_active)
                                <span class="ml-2 text-xs text-gray-400">(Inactive)</span>
                            @endif
                        </span>
                        <div class="flex gap-2">
                            @if($recipient->is_active)
                                <button wire:click="deactivateRecipient({{ $recipient->id }})" class="text-yellow-600 hover:underline">Deactivate</button>
                            @else
                                <button wire:click="activateRecipient({{ $recipient->id }})" class="text-green-600 hover:underline">Activate</button>
                            @endif
                            <button wire:click="confirmDelete({{ $recipient->id }})" class="text-red-600 hover:underline">Delete</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($confirmingDelete)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                    <p>Are you sure you want to delete this recipient?</p>
                    <div class="mt-4 flex gap-2">
                        <button wire:click="deleteRecipient" class="bg-red-600 text-white px-4 py-2 rounded">Delete</button>
                        <button wire:click="$set('confirmingDelete', false)" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    </div>
                </div>
            </div>
        @endif
    </x-settings.layout>
</section>
