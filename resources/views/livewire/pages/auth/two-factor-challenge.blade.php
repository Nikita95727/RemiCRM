<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';

    /**
     * Handle an incoming two factor authentication request.
     */
    public function verify(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user || !$user->hasEnabledTwoFactorAuthentication()) {
            $this->redirect(route('login'));
            return;
        }

        // Verify the code using Google2FA
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        
        if ($google2fa->verifyKey($user->two_factor_secret, $this->code)) {
            // Code is valid, mark as confirmed in session
            session(['two_factor_confirmed' => true]);
            
            $this->redirect(route('contacts'), navigate: false);
        } else {
            // Check if it's a recovery code
            if (in_array($this->code, $user->recoveryCodes())) {
                $user->replaceRecoveryCode($this->code);
                session(['two_factor_confirmed' => true]);
                
                $this->redirect(route('contacts'), navigate: false);
            } else {
                $this->addError('code', 'The provided two factor authentication code was invalid.');
            }
        }
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
    </div>

    <form wire:submit="verify">
        <!-- Two Factor Authentication Code -->
        <div>
            <x-input-label for="code" :value="__('Code')" />
            <x-text-input 
                wire:model="code" 
                id="code" 
                class="block mt-1 w-full" 
                type="text" 
                name="code" 
                required 
                autofocus 
                autocomplete="one-time-code"
                placeholder="000000"
                maxlength="6"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-sm text-gray-600">
        <p>{{ __('If you are having trouble accessing your account, you may use one of your recovery codes.') }}</p>
    </div>
</div>
