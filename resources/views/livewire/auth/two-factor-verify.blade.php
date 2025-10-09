<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

new #[Layout('components.layouts.auth')] class extends Component {
    public $code = '';
    public $code1 = '';
    public $code2 = '';
    public $code3 = '';
    public $code4 = '';
    public $code5 = '';
    public $code6 = '';

    /**
     * Verify the two-factor authentication code.
     */
    public function verifyTwoFactorCode()
    {
        $this->code = $this->code1 . $this->code2 . $this->code3 . $this->code4 . $this->code5 . $this->code6;

        $user = Auth::user();

        if (!Google2FA::verifyKey($user->google2fa_secret, $this->code)) {
            flash()->error('Invalid verification code.');
            return;
        }

        Session::put('2fa_verified', true);

        return redirect()->route('dashboard');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <flux:text class="text-center">
        {{ __('Please enter the 6-digit code from your authenticator app.') }}
    </flux:text>

    @if (Session::has('error'))
        <flux:text class="text-center font-medium !dark:text-red-400 !text-red-600">
            {{ Session::get('error') }}
        </flux:text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <div class="flex gap-2">
            <input type="text" wire:model="code1" maxlength="1"
                class="w-12 h-12 text-center rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white"
                x-on:keyup="$event.target.value ? $event.target.nextElementSibling?.focus() : null"
                x-on:keydown.backspace="$event.target.value == '' ? $event.target.previousElementSibling?.focus() : null" />
            <input type="text" wire:model="code2" maxlength="1"
                class="w-12 h-12 text-center rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white"
                x-on:keyup="$event.target.value ? $event.target.nextElementSibling?.focus() : null"
                x-on:keydown.backspace="$event.target.value == '' ? $event.target.previousElementSibling?.focus() : null" />
            <input type="text" wire:model="code3" maxlength="1"
                class="w-12 h-12 text-center rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white"
                x-on:keyup="$event.target.value ? $event.target.nextElementSibling?.focus() : null"
                x-on:keydown.backspace="$event.target.value == '' ? $event.target.previousElementSibling?.focus() : null" />
            <input type="text" wire:model="code4" maxlength="1"
                class="w-12 h-12 text-center rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white"
                x-on:keyup="$event.target.value ? $event.target.nextElementSibling?.focus() : null"
                x-on:keydown.backspace="$event.target.value == '' ? $event.target.previousElementSibling?.focus() : null" />
            <input type="text" wire:model="code5" maxlength="1"
                class="w-12 h-12 text-center rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white"
                x-on:keyup="$event.target.value ? $event.target.nextElementSibling?.focus() : null"
                x-on:keydown.backspace="$event.target.value == '' ? $event.target.previousElementSibling?.focus() : null" />
            <input type="text" wire:model="code6" maxlength="1"
                class="w-12 h-12 text-center rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white"
                x-on:keydown.backspace="$event.target.value == '' ? $event.target.previousElementSibling?.focus() : null" />
        </div>

        <flux:button wire:click="verifyTwoFactorCode" variant="primary" class="w-full">
            {{ __('Verify Code') }}
        </flux:button>

        <flux:link class="text-sm cursor-pointer" wire:click="logout">
            {{ __('Log out') }}
        </flux:link>
    </div>
</div>
