<?php

declare(strict_types=1);

namespace App\Modules\Integration\Transformers;

use App\Modules\Contact\DTOs\CreateContactDTO;

interface ContactTransformerInterface
{
    /**
     * Transform raw provider data to contact DTOs
     *
     * @param array<string, mixed> $rawData Raw data from provider API
     * @param int $userId User ID for contacts
     * @return array<CreateContactDTO>
     */
    public function transform(array $rawData, int $userId): array;

    /**
     * Get provider name this transformer handles
     */
    public function getProvider(): string;
}
