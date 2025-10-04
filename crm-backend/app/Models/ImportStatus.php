<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportStatus extends Model
{
    use HasFactory;

    protected $table = 'import_status';

    protected $fillable = [
        'user_id',
        'provider',
        'status',
        'total_items',
        'processed_items',
        'message',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'processed_items' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_items === 0) {
            return 0;
        }

        return (int) round(($this->processed_items / $this->total_items) * 100);
    }

    public function getStatusMessageAttribute(): string
    {
        return match ($this->status) {
            'importing' => 'Importing contacts...',
            'tagging' => 'Auto-tagging contacts...',
            'completed' => 'Import completed',
            'failed' => 'Import failed',
            default => 'Processing...',
        };
    }

    public static function startImport(int $userId, string $provider, int $totalItems = 0): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'provider' => $provider],
            [
                'status' => 'importing',
                'total_items' => $totalItems,
                'processed_items' => 0,
                'message' => null,
            ]
        );
    }

    public static function updateProgress(int $userId, string $provider, int $processedItems, ?string $message = null): void
    {
        self::where('user_id', $userId)
            ->where('provider', $provider)
            ->update([
                'processed_items' => $processedItems,
                'message' => $message,
            ]);
    }

    public static function setTagging(int $userId, string $provider): void
    {
        self::where('user_id', $userId)
            ->where('provider', $provider)
            ->update([
                'status' => 'tagging',
                'message' => 'Auto-tagging contacts...',
            ]);
    }

    public static function completeImport(int $userId, string $provider): void
    {
        self::where('user_id', $userId)
            ->where('provider', $provider)
            ->update([
                'status' => 'completed',
                'message' => 'Import completed successfully',
            ]);
        
        // Schedule cleanup of completed status after 10 seconds
        dispatch(function () use ($userId, $provider) {
            self::where('user_id', $userId)
                ->where('provider', $provider)
                ->where('status', 'completed')
                ->delete();
        })->delay(now()->addSeconds(10));
    }

    public static function failImport(int $userId, string $provider, string $error): void
    {
        self::where('user_id', $userId)
            ->where('provider', $provider)
            ->update([
                'status' => 'failed',
                'message' => $error,
            ]);
    }
}