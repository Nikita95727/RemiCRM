<?php

declare(strict_types=1);

namespace App\Shared\DTOs;

use App\Shared\Enums\ContactSource;

readonly class ContactDTO
{
    /**
     * @param array<string, mixed>|null $additionalInfo
     */
    public function __construct(
        public string $name,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $telegramUsername = null,
        public ?string $whatsappNumber = null,
        public ContactSource $source = ContactSource::MANUAL,
        public ?array $additionalInfo = null,
        public ?string $notes = null,
        public bool $isActive = true,
        public ?\DateTime $lastContactAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            telegramUsername: $data['telegram_username'] ?? null,
            whatsappNumber: $data['whatsapp_number'] ?? null,
            source: ContactSource::from($data['source'] ?? 'manual'),
            additionalInfo: $data['additional_info'] ?? null,
            notes: $data['notes'] ?? null,
            isActive: $data['is_active'] ?? true,
            lastContactAt: isset($data['last_contact_at'])
                ? new \DateTime($data['last_contact_at'])
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'telegram_username' => $this->telegramUsername,
            'whatsapp_number' => $this->whatsappNumber,
            'source' => $this->source->value,
            'additional_info' => $this->additionalInfo,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
            'last_contact_at' => $this->lastContactAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param array<string, mixed>|null $additionalInfo
     */
    public static function forTelegram(
        string $name,
        string $telegramUsername,
        ?string $phone = null,
        ?array $additionalInfo = null
    ): self {
        return new self(
            name: $name,
            phone: $phone,
            telegramUsername: $telegramUsername,
            source: ContactSource::TELEGRAM,
            additionalInfo: $additionalInfo,
        );
    }

    /**
     * @param array<string, mixed>|null $additionalInfo
     */
    public static function forWhatsApp(
        string $name,
        string $whatsappNumber,
        ?string $phone = null,
        ?array $additionalInfo = null
    ): self {
        return new self(
            name: $name,
            phone: $phone,
            whatsappNumber: $whatsappNumber,
            source: ContactSource::WHATSAPP,
            additionalInfo: $additionalInfo,
        );
    }

    /**
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->name))) {
            $errors['name'] = 'Name is required';
        }

        if ($this->source === ContactSource::TELEGRAM && empty($this->telegramUsername)) {
            $errors['telegram_username'] = 'Telegram username is required for Telegram contacts';
        }

        if ($this->source === ContactSource::WHATSAPP && empty($this->whatsappNumber)) {
            $errors['whatsapp_number'] = 'WhatsApp number is required for WhatsApp contacts';
        }

        return $errors;
    }

    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
