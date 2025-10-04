<?php

declare(strict_types=1);

namespace App\Modules\Integration\Models;

use App\Shared\Enums\ContactSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegratedAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'unipile_account_id',
        'provider',
        'account_name',
        'account_email',
        'account_username',
        'status',
        'last_sync_at',
        'sync_enabled',
        'metadata',
        'access_token',
        'error_message',
        'last_error_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'last_error_at' => 'datetime',
        'sync_enabled' => 'boolean',
        'metadata' => 'array',
        'provider' => ContactSource::class,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Contact\Models\Contact::class,
            'contact_integrations',
            'integrated_account_id',
            'contact_id'
        )->withPivot(['external_id', 'last_synced_at'])->withTimestamps();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<IntegratedAccount> $query
     * @return \Illuminate\Database\Eloquent\Builder<IntegratedAccount>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('sync_enabled', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<IntegratedAccount> $query
     * @return \Illuminate\Database\Eloquent\Builder<IntegratedAccount>
     */
    public function scopeByProvider($query, ContactSource $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<IntegratedAccount> $query
     * @return \Illuminate\Database\Eloquent\Builder<IntegratedAccount>
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->sync_enabled;
    }

    public function needsResync(): bool
    {
        if (! $this->last_sync_at) {
            return true;
        }

        return $this->last_sync_at->diffInHours(now()) >= 24;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->account_name ?: $this->account_email ?: $this->account_username ?: 'Unknown Account';
    }

    public function getProviderLabelAttribute(): string
    {
        return $this->provider->getLabel();
    }

    public function getProviderIconAttribute(): string
    {
        return $this->provider->getIcon();
    }

    public function getProviderCssClassAttribute(): string
    {
        return $this->provider->getCssClass();
    }
}
