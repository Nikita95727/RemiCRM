<?php

declare(strict_types=1);

namespace App\Modules\Integration\Transformers;

use App\Modules\Contact\DTOs\CreateContactDTO;
use Illuminate\Support\Facades\Log;

class TelegramContactTransformer implements ContactTransformerInterface
{
    /**
     * @param array<string, mixed> $rawData
     * @return array<CreateContactDTO>
     */
    public function transform(array $rawData, int $userId): array
    {
        $contacts = [];
        $processedProviderIds = [];
        $totalChats = count($rawData['items'] ?? []);
        $privateChats = 0;
        $skippedGroups = 0;

        Log::info('TelegramContactTransformer: Starting transformation', [
            'total_chats' => $totalChats,
            'user_id' => $userId,
        ]);

        foreach ($rawData['items'] ?? [] as $chat) {
            // Filter only private chats (type = 0)
            $chatType = $chat['type'] ?? null;
            if ($chatType !== 0) {
                $skippedGroups++;
                Log::debug('TelegramContactTransformer: Skipping non-private chat', [
                    'chat_type' => $chatType,
                    'chat_type_string' => $this->getChatTypeString($chatType),
                    'chat_name' => $chat['name'] ?? 'unnamed',
                ]);
                continue;
            }

            $privateChats++;
            $transformedContact = $this->transformSingleChat($chat, $userId);
            
            if ($transformedContact && !in_array($transformedContact->providerId, $processedProviderIds)) {
                $contacts[] = $transformedContact;
                $processedProviderIds[] = $transformedContact->providerId;
            }
        }

        Log::info('TelegramContactTransformer: Transformation completed', [
            'total_chats' => $totalChats,
            'private_chats' => $privateChats,
            'skipped_groups' => $skippedGroups,
            'contacts_transformed' => count($contacts),
        ]);

        return $contacts;
    }

    public function getProvider(): string
    {
        return 'telegram';
    }

    /**
     * @param array<string, mixed> $chat
     */
    private function transformSingleChat(array $chat, int $userId): ?CreateContactDTO
    {
        $providerId = $chat['attendee_provider_id'] ?? null;
        $name = $chat['name'] ?? null;

        if (!$providerId) {
            return null;
        }

        // For Telegram, if no name, try to extract from provider_id or use fallback
        if (!$name) {
            if (preg_match('/^(\d+)$/', $providerId, $matches)) {
                // Pure number - likely phone number
                $name = '+' . $matches[1];
            } else {
                // Username or other format
                $name = '@' . $providerId;
            }
        }

        $lastMessageDate = $chat['last_message_date'] ?? $chat['timestamp'] ?? null;
        $chatType = $this->getChatTypeString($chat['type'] ?? null);
        $messageCount = $chat['message_count'] ?? 0;

        $notes = sprintf(
            'Imported from Telegram | Type: %s | Messages: %d%s',
            $chatType,
            $messageCount,
            $lastMessageDate ? ' | Last activity: ' . $lastMessageDate : ''
        );

        return CreateContactDTO::fromSyncData(
            userId: $userId,
            name: $name,
            provider: 'telegram',
            notes: $notes,
            providerId: $providerId
        );
    }

    private function getChatTypeString(?int $type): string
    {
        return match ($type) {
            0 => 'private',
            1 => 'group',
            2 => 'supergroup',
            3 => 'channel',
            default => 'unknown',
        };
    }
}
