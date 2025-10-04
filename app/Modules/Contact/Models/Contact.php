<?php

declare(strict_types=1);

namespace App\Modules\Contact\Models;

use App\Shared\Enums\ContactSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'sources',
        'notes',
        'tags',
        'user_id',
        'provider_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'sources' => 'array',
    ];

    // Relations
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function integrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Integration\Models\ContactIntegration::class);
    }

    // Scopes for filtering
    /**
     * @param \Illuminate\Database\Eloquent\Builder<Contact> $query
     * @return \Illuminate\Database\Eloquent\Builder<Contact>
     */
    public function scopeBySource($query, string $source)
    {
        return $query->whereJsonContains('sources', $source);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<Contact> $query
     * @return \Illuminate\Database\Eloquent\Builder<Contact>
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<Contact> $query
     * @return \Illuminate\Database\Eloquent\Builder<Contact>
     */
    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                ->orWhere('notes', 'LIKE', "%{$searchTerm}%");

            // Search in tags JSON array - use LIKE for partial matches
            $q->orWhere('tags', 'LIKE', "%\"{$searchTerm}\"%")
              ->orWhere('tags', 'LIKE', "%{$searchTerm}%");

            // Search by source values and labels
            foreach (ContactSource::cases() as $source) {
                if (stripos($source->getLabel(), $searchTerm) !== false || stripos($source->value, $searchTerm) !== false) {
                    $q->orWhere('sources', 'LIKE', "%\"{$source->value}\"%");
                }
            }
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<Contact> $query
     * @return \Illuminate\Database\Eloquent\Builder<Contact>
     */
    public function scopeFastSearch($query, string $searchTerm)
    {
        return $query->whereFullText(['name', 'notes'], $searchTerm)
            ->orWhereJsonContains('tags', $searchTerm)
            ->orWhereJsonContains('sources', $searchTerm);
    }


    // Accessors
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        return $this->phone ? preg_replace('/[^0-9]/', '', $this->phone) : null;
    }

    public function getTagsListAttribute(): string
    {
        return $this->tags ? implode(', ', $this->tags) : '';
    }

    /**
     * @return array<string>
     */
    public function getSourcesListAttribute(): array
    {
        return $this->sources ?? [];
    }

    /**
     * @return array<int, object>
     */
    public function getSourceObjectsAttribute(): array
    {
        if (empty($this->sources)) {
            return [];
        }

        return array_map(function ($sourceValue) {
            return ContactSource::from($sourceValue);
        }, $this->sources);
    }

    public function getPrimarySourceAttribute(): ?ContactSource
    {
        if (empty($this->sources)) {
            return null;
        }

        return ContactSource::from($this->sources[0]);
    }

    // Mutators
    /**
     * @param string|null $value
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value ? preg_replace('/[^0-9+]/', '', $value) : null;
    }

    /**
     * @param array<string>|string|null $value
     */
    public function setTagsAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['tags'] = json_encode(array_map('trim', explode(',', $value)));
        } elseif (is_array($value)) {
            $this->attributes['tags'] = json_encode(array_filter($value));
        } else {
            $this->attributes['tags'] = null;
        }
    }

    /**
     * @param array<string>|string|null $value
     */
    public function setSourcesAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['sources'] = json_encode([$value]);
        } elseif (is_array($value)) {
            $filtered = array_unique(array_filter($value));
            $this->attributes['sources'] = json_encode($filtered);
        } else {
            $this->attributes['sources'] = json_encode([]);
        }
    }

    // Helper methods
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (! in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $this->tags = array_values(array_filter($tags, fn ($t) => $t !== $tag));
    }

    public function hasSource(string $source): bool
    {
        return in_array($source, $this->sources ?? []);
    }

    public function addSource(string $source): void
    {
        $sources = $this->sources ?? [];
        if (! in_array($source, $sources)) {
            $sources[] = $source;
            $this->sources = $sources;
        }
    }

    public function removeSource(string $source): void
    {
        $sources = $this->sources ?? [];
        $this->sources = array_values(array_filter($sources, fn ($s) => $s !== $source));
    }
}
