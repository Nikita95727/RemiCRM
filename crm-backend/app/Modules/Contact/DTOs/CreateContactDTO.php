<?php

declare(strict_types=1);

namespace App\Modules\Contact\DTOs;

class CreateContactDTO
{
    /**
     * @param array<string> $sources
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly array $sources = [],
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $notes = null,
        public readonly ?string $providerId = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'sources' => $this->sources,
            'email' => $this->email,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'provider_id' => $this->providerId,
        ];
    }

    public static function fromSyncData(
        int $userId, 
        string $name, 
        string $provider, 
        ?string $notes = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $providerId = null
    ): self {
        return new self(
            userId: $userId,
            name: $name,
            sources: [$provider],
            email: $email,
            phone: $phone,
            notes: $notes ?? 'Imported from '.ucfirst($provider),
            providerId: $providerId
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            name: $data['name'],
            sources: $data['sources'] ?? [],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            notes: $data['notes'] ?? null
        );
    }
}
