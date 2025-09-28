<?php

declare(strict_types=1);

namespace App\Modules\Integration\DTOs;

class CreateHostedAuthLinkDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly int $userId,
        public readonly ?string $redirectUrl = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'user_id' => $this->userId,
            'redirect_url' => $this->redirectUrl,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            provider: $data['provider'],
            userId: $data['user_id'],
            redirectUrl: $data['redirect_url'] ?? null
        );
    }
}
