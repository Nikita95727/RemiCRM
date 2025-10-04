<?php

declare(strict_types=1);

namespace App\Modules\Integration\DTOs;

class SyncAccountDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $unipileAccountId,
        public readonly string $provider,
        public readonly string $accountName,
        public readonly string $status = 'active',
        public readonly bool $syncEnabled = true,
        /** @var array<string, mixed>|null $metadata */
        public readonly ?array $metadata = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'unipile_account_id' => $this->unipileAccountId,
            'provider' => $this->provider,
            'account_name' => $this->accountName,
            'status' => $this->status,
            'sync_enabled' => $this->syncEnabled,
            'last_sync_at' => now(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @param array<string, mixed> $accountData
     */
    public static function fromUnipileData(int $userId, array $accountData): self
    {
        return new self(
            userId: $userId,
            unipileAccountId: $accountData['id'],
            provider: strtolower($accountData['type']),
            accountName: $accountData['name'],
            status: 'active',
            syncEnabled: true,
            metadata: $accountData
        );
    }
}
