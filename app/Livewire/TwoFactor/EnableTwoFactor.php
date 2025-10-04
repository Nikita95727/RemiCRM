<?php

namespace App\Livewire\TwoFactor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class EnableTwoFactor extends Component
{
    public $user;
    public $qrCodeSvg;
    public $secretKey;
    public $recoveryCodes = [];
    public $showRecoveryCodes = false;
    public $confirmingTwoFactorAuthentication = false;
    public $code = '';

    public function mount()
    {
        $this->user = Auth::user();
        
        if ($this->user->hasEnabledTwoFactorAuthentication()) {
            $this->confirmingTwoFactorAuthentication = true;
        }
    }

    public function enableTwoFactorAuthentication()
    {
        $this->user = Auth::user();
        
        $google2fa = app(Google2FA::class);
        $this->secretKey = $google2fa->generateSecretKey();
        
        $this->user->forceFill([
            'two_factor_secret' => encrypt($this->secretKey),
        ])->save();

        $this->generateRecoveryCodes();
        $this->generateQrCode();
    }

    public function confirmTwoFactorAuthentication()
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $google2fa = app(Google2FA::class);
        
        if ($google2fa->verifyKey($this->secretKey, $this->code)) {
            $this->user->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            $this->confirmingTwoFactorAuthentication = false;
            $this->showRecoveryCodes = true;
            
            session()->flash('status', 'Two-factor authentication has been enabled.');
        } else {
            $this->addError('code', 'The provided two factor authentication code was invalid.');
        }
    }

    public function disableTwoFactorAuthentication()
    {
        $this->user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->confirmingTwoFactorAuthentication = false;
        $this->showRecoveryCodes = false;
        $this->recoveryCodes = [];
        
        session()->flash('status', 'Two-factor authentication has been disabled.');
    }

    protected function generateRecoveryCodes()
    {
        $this->recoveryCodes = Collection::times(8, function () {
            return strtoupper(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 10));
        })->toArray();

        $this->user->forceFill([
            'two_factor_recovery_codes' => encrypt($this->recoveryCodes),
        ])->save();
    }

    protected function generateQrCode()
    {
        $google2fa = app(Google2FA::class);
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->user->email,
            $this->secretKey
        );

        $this->qrCodeSvg = app(Google2FAQRCode::class)->getQRCodeInline(
            config('app.name'),
            $this->user->email,
            $this->secretKey
        );
    }

    public function render()
    {
        return view('livewire.two-factor.enable-two-factor');
    }
}
