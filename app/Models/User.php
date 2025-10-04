<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_setup_completed',
        'two_factor_enabled_by_user',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'two_factor_setup_completed' => 'boolean',
            'two_factor_enabled_by_user' => 'boolean',
        ];
    }

    /**
     * Determine if two-factor authentication has been enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) 
            && !is_null($this->two_factor_confirmed_at)
            && $this->two_factor_enabled_by_user;
    }

    /**
     * Check if user needs to setup 2FA for the first time.
     */
    public function needsTwoFactorSetup(): bool
    {
        return !$this->two_factor_setup_completed;
    }

    /**
     * Check if 2FA is disabled by user choice.
     */
    public function hasTwoFactorDisabled(): bool
    {
        return !$this->two_factor_enabled_by_user;
    }

    /**
     * Get the user's two-factor authentication recovery codes.
     */
    public function recoveryCodes(): array
    {
        return $this->two_factor_recovery_codes ?? [];
    }

    /**
     * Replace a given recovery code with a new one.
     */
    public function replaceRecoveryCode(string $code): void
    {
        $this->forceFill([
            'two_factor_recovery_codes' => array_values(
                array_diff($this->recoveryCodes(), [$code])
            ),
        ])->save();
    }
}
