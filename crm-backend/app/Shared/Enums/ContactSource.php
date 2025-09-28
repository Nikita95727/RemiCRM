<?php

declare(strict_types=1);

namespace App\Shared\Enums;

enum ContactSource: string
{
    case CRM = 'crm';
    case MANUAL = 'manual'; // Alias for CRM for backward compatibility
    case TELEGRAM = 'telegram';
    case WHATSAPP = 'whatsapp';
    case GMAIL = 'gmail';

    public function getLabel(): string
    {
        return match ($this) {
            self::CRM => 'CRM',
            self::MANUAL => 'Manual',
            self::TELEGRAM => 'Telegram',
            self::WHATSAPP => 'WhatsApp',
            self::GMAIL => 'Gmail',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];
        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->getLabel();
        }

        return $labels;
    }

    public function isExternal(): bool
    {
        return $this !== self::CRM && $this !== self::MANUAL;
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::CRM => 'bg-blue-100 text-blue-800',
            self::MANUAL => 'bg-blue-100 text-blue-800',
            self::TELEGRAM => 'bg-sky-100 text-sky-800',
            self::WHATSAPP => 'bg-green-100 text-green-800',
            self::GMAIL => 'bg-red-100 text-red-800',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            // User icon for CRM and Manual
            self::CRM, self::MANUAL => 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z',
            // Telegram paper plane icon
            self::TELEGRAM => 'M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 2.1 11.75c-1.16-.35-1.16-.83.26-1.22L21.26 5.05c.84-.35 1.61.2 1.37 1.22L20.84 18.65c-.19.95-.74 1.19-1.5.74L14.25 16.5 11.5 19.36c-.25.24-.46.45-.72.45z',
            // WhatsApp phone icon
            self::WHATSAPP => 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z',
            // Gmail envelope icon
            self::GMAIL => 'M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z',
        };
    }
}
