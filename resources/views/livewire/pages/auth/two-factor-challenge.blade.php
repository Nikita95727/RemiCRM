<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use PragmaRX\Google2FA\Google2FA;

new #[Layout('layouts.guest')] class extends Component
{
    public $code = '';
    public $recovery_code = '';
    public $useRecoveryCode = false;

    public function verifyCode()
    {
        $this->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        $valid = $google2fa->verifyKey($secret, $this->code);

        if (!$valid) {
            $this->addError('code', 'The code you entered is invalid.');
            return;
        }

        // Mark 2FA as verified for this session
        session()->put('two_factor_verified', true);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function verifyRecoveryCode()
    {
        $this->validate([
            'recovery_code' => 'required|string',
        ]);

        $user = auth()->user();
        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (!in_array($this->recovery_code, $codes)) {
            $this->addError('recovery_code', 'The recovery code is invalid.');
            return;
        }

        // Remove used recovery code
        $user->replaceRecoveryCode($this->recovery_code);

        // Mark 2FA as verified for this session
        session()->put('two_factor_verified', true);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function toggleRecoveryMode()
    {
        $this->useRecoveryCode = !$this->useRecoveryCode;
        $this->reset(['code', 'recovery_code']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        Please confirm access to your account by entering the authentication code provided by your authenticator application.
    </div>

    @if (!$useRecoveryCode)
        <!-- Regular Code Input -->
        <form wire:submit="verifyCode">
            <div>
                <x-input-label for="code" value="Code" />
                <x-text-input wire:model="code" 
                              id="code" 
                              class="block mt-1 w-full" 
                              type="text" 
                              maxlength="6"
                              inputmode="numeric"
                              required 
                              autofocus 
                              autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" 
                        wire:click="toggleRecoveryMode"
                        class="text-sm text-gray-600 underline hover:text-gray-900">
                    Use a recovery code
                </button>

                <x-primary-button>
                    Verify
                </x-primary-button>
            </div>
        </form>
    @else
        <!-- Recovery Code Input -->
        <form wire:submit="verifyRecoveryCode">
            <div>
                <x-input-label for="recovery_code" value="Recovery Code" />
                <x-text-input wire:model="recovery_code" 
                              id="recovery_code" 
                              class="block mt-1 w-full" 
                              type="text" 
                              required 
                              autofocus />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <button type="button" 
                        wire:click="toggleRecoveryMode"
                        class="text-sm text-gray-600 underline hover:text-gray-900">
                    Use an authentication code
                </button>

                <x-primary-button>
                    Verify
                </x-primary-button>
            </div>
        </form>
    @endif
</div>

