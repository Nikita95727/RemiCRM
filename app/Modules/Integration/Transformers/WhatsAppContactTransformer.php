<?php

declare(strict_types=1);

namespace App\Modules\Integration\Transformers;

use App\Modules\Contact\DTOs\CreateContactDTO;
use Illuminate\Support\Facades\Log;

class WhatsAppContactTransformer implements ContactTransformerInterface
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

        Log::info('WhatsAppContactTransformer: Starting transformation', [
            'total_chats' => $totalChats,
            'user_id' => $userId,
        ]);

        foreach ($rawData['items'] ?? [] as $chat) {
            // Log all chat data for debugging
            Log::info('WhatsAppContactTransformer: Processing chat', [
                'chat_data' => $chat,
                'chat_type' => $chat['type'] ?? null,
                'chat_name' => $chat['name'] ?? null,
                'provider_id' => $chat['attendee_provider_id'] ?? null,
            ]);
            
            // Filter only private chats (type = 0 for WhatsApp)
            $chatType = $chat['type'] ?? null;
            if ($chatType !== 0) {
                $skippedGroups++;
                Log::debug('WhatsAppContactTransformer: Skipping non-private chat', [
                    'chat_type' => $chatType,
                    'chat_name' => $chat['name'] ?? 'unnamed',
                    'provider_id' => $chat['attendee_provider_id'] ?? 'unknown',
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

        Log::info('WhatsAppContactTransformer: Transformation completed', [
            'total_chats' => $totalChats,
            'private_chats' => $privateChats,
            'skipped_groups' => $skippedGroups,
            'contacts_transformed' => count($contacts),
        ]);

        return $contacts;
    }

    public function getProvider(): string
    {
        return 'whatsapp';
    }

    /**
     * @param array<string, mixed> $chat
     */
    private function transformSingleChat(array $chat, int $userId): ?CreateContactDTO
    {
        $providerId = $chat['attendee_provider_id'] ?? null;
        $chatId = $chat['id'] ?? null;  // THIS is the ID needed for fetching messages!
        $name = $chat['name'] ?? null;

        if (!$providerId) {
            return null;
        }

        // WhatsApp-specific logic: extract phone number from provider_id format
        if (!$name) {
            // Try multiple WhatsApp provider_id formats
            if (preg_match('/^(\d+)@s\.whatsapp\.net$/', $providerId, $matches)) {
                // Format: "380502865307@s.whatsapp.net"
                $phoneNumber = $matches[1];
                $name = '+' . $phoneNumber;
                Log::debug('WhatsAppContactTransformer: Generated name from phone (format 1)', [
                    'provider_id' => $providerId,
                    'generated_name' => $name,
                ]);
            } elseif (preg_match('/^(\d+)@/', $providerId, $matches)) {
                // Format: "380502865307@c.us" or similar
                $phoneNumber = $matches[1];
                $name = '+' . $phoneNumber;
                Log::debug('WhatsAppContactTransformer: Generated name from phone (format 2)', [
                    'provider_id' => $providerId,
                    'generated_name' => $name,
                ]);
            } elseif (preg_match('/^\d+$/', $providerId)) {
                // Format: just phone number "380502865307"
                $name = '+' . $providerId;
                Log::debug('WhatsAppContactTransformer: Generated name from phone (format 3)', [
                    'provider_id' => $providerId,
                    'generated_name' => $name,
                ]);
            } else {
                // Fallback: use provider_id as name
                $name = $providerId;
                Log::info('WhatsAppContactTransformer: Using provider_id as name (unknown format)', [
                    'provider_id' => $providerId,
                    'generated_name' => $name,
                ]);
            }
        }

        $lastMessageDate = $chat['timestamp'] ?? null;
        $messageCount = $chat['unread_count'] ?? 0; // WhatsApp uses unread_count
        $isArchived = $chat['archived'] ?? false;
        $isPinned = $chat['pinned'] ?? false;

        $notes = sprintf(
            'Imported from WhatsApp | Messages: %d%s%s%s',
            $messageCount,
            $lastMessageDate ? ' | Last activity: ' . $lastMessageDate : '',
            $isArchived ? ' | Archived' : '',
            $isPinned ? ' | Pinned' : ''
        );

        // Extract phone number for the phone field
        $phone = null;
        if (preg_match('/^(\d+)@/', $providerId, $matches)) {
            $phone = '+' . $matches[1];
        } elseif (preg_match('/^\d+$/', $providerId)) {
            $phone = '+' . $providerId;
        }

        return CreateContactDTO::fromSyncData(
            userId: $userId,
            name: $name,
            provider: 'whatsapp',
            notes: $notes,
            email: null,
            phone: $phone,
            providerId: $providerId,
            chatId: $chatId  // Pass the actual chat ID for message fetching
        );
    }
}
