<?php

namespace App\Livewire;

use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TwoFactorAuthentication extends Component
{
    public $secret;
    public $qrCode;
    public $recoveryCodes = [];
    public $confirmationCode = '';
    public $showingQrCode = false;
    public $showingRecoveryCodes = false;

    public function mount()
    {
        $this->checkCurrentState();
    }

    protected function checkCurrentState()
    {
        $user = auth()->user();
        
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            // 2FA уже полностью настроена
            $this->showingRecoveryCodes = false;
            $this->showingQrCode = false;
        }
    }

    public function enableTwoFactor()
    {
        $google2fa = new Google2FA();
        $this->secret = $google2fa->generateSecretKey();
        
        // Генерируем QR код
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            auth()->user()->email,
            $this->secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $this->qrCode = $writer->writeString($qrCodeUrl);

        // Сохраняем секрет (но еще не подтверждаем)
        auth()->user()->forceFill([
            'two_factor_secret' => encrypt($this->secret),
        ])->save();

        $this->showingQrCode = true;
        $this->showingRecoveryCodes = false;
    }

    public function confirmTwoFactor()
    {
        $this->validate([
            'confirmationCode' => 'required|string|size:6',
        ]);

        $google2fa = new Google2FA();
        $user = auth()->user();

        $secret = decrypt($user->two_factor_secret);

        $valid = $google2fa->verifyKey($secret, $this->confirmationCode);

        if (!$valid) {
            $this->addError('confirmationCode', 'The code you entered is invalid.');
            return;
        }

        // Генерируем коды восстановления
        $this->recoveryCodes = Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);
        })->all();

        // Подтверждаем 2FA
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($this->recoveryCodes)),
            'two_factor_confirmed_at' => now(),
            'two_factor_setup_completed' => true,
            'two_factor_enabled_by_user' => true,
        ])->save();

        $this->showingQrCode = false;
        $this->showingRecoveryCodes = true;
        $this->confirmationCode = '';

        session()->flash('status', 'Two-factor authentication has been enabled successfully!');
    }

    public function regenerateRecoveryCodes()
    {
        $user = auth()->user();

        if (!$user->hasTwoFactorEnabled()) {
            return;
        }

        $this->recoveryCodes = Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);
        })->all();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($this->recoveryCodes)),
        ])->save();

        $this->showingRecoveryCodes = true;

        session()->flash('status', 'Recovery codes have been regenerated.');
    }

    public function showRecoveryCodes()
    {
        $user = auth()->user();

        if (!$user->hasTwoFactorEnabled()) {
            return;
        }

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $this->recoveryCodes = $codes;
        $this->showingRecoveryCodes = true;
    }

    public function disableTwoFactor()
    {
        auth()->user()->forceFill([
            'two_factor_enabled_by_user' => false,
        ])->save();

        $this->checkCurrentState();

        session()->flash('status', 'Two-factor authentication has been disabled. You can re-enable it at any time.');
    }

    public function enableTwoFactorAgain()
    {
        $user = auth()->user();
        
        // Если 2FA уже была настроена, просто включаем её обратно
        if ($user->two_factor_setup_completed && $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_enabled_by_user' => true,
            ])->save();

            session()->flash('status', 'Two-factor authentication has been re-enabled!');
        } else {
            // Если нет, запускаем процесс настройки
            $this->enableTwoFactor();
        }
    }

    public function render()
    {
        return view('livewire.two-factor-authentication');
    }
}
