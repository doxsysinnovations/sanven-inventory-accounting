<?php

use Livewire\Volt\Component;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

new class extends Component {
    public $qrCodeUrl;
    public $secret;
    public $enabled;

    public function mount()
    {
        $user = Auth::user();

        if ($user->google2fa_enabled) {
            $this->enabled = true;
        } else {
            $this->enabled = false;
            $this->generateSecret();
        }
    }

    public function generateSecret()
    {
        $user = Auth::user();
        $this->secret = Google2FA::generateSecretKey();
        $user->google2fa_secret = $this->secret;
        $user->save();

        $this->qrCodeUrl = Google2FA::getQRCodeUrl(config('app.name'), $user->email, $this->secret);
    }

    public function enable2FA()
    {
        $user = Auth::user();
        $user->google2fa_enabled = true;
        $user->save();

        $this->enabled = true;
        flash()->success('2FA enabled successfully.');

        activity('enable-2fa')
            ->causedBy(Auth::user())
            ->performedOn(Auth::user())
            ->withProperties(['ip' => request()->ip()])
            ->log('User enabled 2FA');
    }

    public function disable2FA()
    {
        $user = Auth::user();
        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->save();

        $this->enabled = false;
        flash()->success('2FA disabled successfully.');

        activity('disable-2fa')
            ->causedBy(Auth::user())
            ->performedOn(Auth::user())
            ->withProperties(['ip' => request()->ip()])
            ->log('User disabled 2FA');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Two-Factor-Authentication Configuration')" :subheading="__('Enable or Disable 2FA')">

        @if ($enabled)
            <p class="text-green-500">2FA is enabled</p>
            <button wire:click="disable2FA" class="bg-red-500 text-white px-4 py-2 rounded mt-4">Disable 2FA</button>
        @else
            <p class="text-gray-600">2FA is disabled</p>

            @if ($qrCodeUrl)
                <div class="mt-4">
                    <p>Scan this QR code with Google Authenticator:</p>
                    <div class="mt-2 border p-4 bg-white" style="width: 232px">
                        <!-- Use the QrCode facade to generate the QR code image -->
                        <div class="flex justify-center">
                            {!! QrCode::size(200)->generate($qrCodeUrl) !!}
                        </div>
                    </div>
                </div>
            @endif

            <button wire:click="enable2FA" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Enable 2FA</button>
        @endif
    </x-settings.layout>
</section>
