<?php

declare(strict_types=1);

namespace App\Modules\Integration\Transformers;

use App\Modules\Contact\DTOs\CreateContactDTO;
use Illuminate\Support\Facades\Log;

class GmailContactTransformer implements ContactTransformerInterface
{
    /**
     * @param array<string, mixed> $rawData
     * @return array<CreateContactDTO>
     */
    public function transform(array $rawData, int $userId): array
    {
        $contacts = [];
        $processedEmails = [];

        Log::info('GmailContactTransformer: Starting transformation', [
            'total_items' => count($rawData['items'] ?? []),
            'user_id' => $userId,
        ]);

        foreach ($rawData['items'] ?? [] as $item) {
            $transformedContact = $this->transformSingleItem($item, $userId);
            
            if ($transformedContact && !in_array($transformedContact->email, $processedEmails)) {
                $contacts[] = $transformedContact;
                $processedEmails[] = $transformedContact->email;
            }
        }

        Log::info('GmailContactTransformer: Transformation completed', [
            'contacts_transformed' => count($contacts),
        ]);

        return $contacts;
    }

    public function getProvider(): string
    {
        return 'gmail';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function transformSingleItem(array $item, int $userId): ?CreateContactDTO
    {
        $email = $item['from'] ?? $item['to'] ?? $item['email'] ?? null;
        $name = $item['from_name'] ?? $item['sender_name'] ?? $item['name'] ?? null;

        if (!$email) {
            return null;
        }

        // Gmail-specific logic: extract name from email if no name provided
        if (!$name) {
            $name = $this->extractNameFromEmail($email);
        }

        $subject = $item['subject'] ?? '';
        $timestamp = $item['timestamp'] ?? $item['date'] ?? null;
        $messageCount = $item['thread_length'] ?? 1;

        $notes = sprintf(
            'Imported from Gmail | Messages: %d%s%s',
            $messageCount,
            $timestamp ? ' | Last activity: ' . $timestamp : '',
            $subject ? ' | Last subject: ' . substr($subject, 0, 50) : ''
        );

        return CreateContactDTO::fromSyncData(
            userId: $userId,
            name: $name,
            provider: 'gmail',
            notes: $notes,
            email: $email
        );
    }

    private function extractNameFromEmail(string $email): string
    {
        $username = explode('@', $email)[0];
        return ucwords(str_replace(['.', '_', '-'], ' ', $username));
    }
}
