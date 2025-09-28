<?php

declare(strict_types=1);

namespace App\Modules\Integration\Models;

use App\Modules\Contact\Models\Contact;
use Illuminate\Database\Eloquent\Model;

/**
 * Contact Integration Model
 * 
 * Represents the relationship between contacts and their integration sources.
 */
class ContactIntegration extends Model
{
    protected $fillable = [
        'contact_id',
        'integrated_account_id',
        'provider_contact_id',
        'last_synced_at',
        'sync_status',
        'metadata',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function contact(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function integratedAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IntegratedAccount::class);
    }
}
