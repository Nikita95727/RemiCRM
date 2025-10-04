<?php

declare(strict_types=1);

namespace App\Modules\Integration\Transformers;

use InvalidArgumentException;

class ContactTransformerFactory
{
    /** @var array<string, ContactTransformerInterface> */
    private array $transformers = [];

    public function __construct()
    {
        $this->registerTransformers();
    }

    public function create(string $provider): ContactTransformerInterface
    {
        if (!isset($this->transformers[$provider])) {
            throw new InvalidArgumentException("No transformer found for provider: {$provider}");
        }

        return $this->transformers[$provider];
    }

    /**
     * @return array<string>
     */
    public function getSupportedProviders(): array
    {
        return array_keys($this->transformers);
    }

    private function registerTransformers(): void
    {
        $this->transformers = [
            'telegram' => new TelegramContactTransformer(),
            'whatsapp' => new WhatsAppContactTransformer(),
            'google_oauth' => new GmailContactTransformer(),
        ];
    }
}
