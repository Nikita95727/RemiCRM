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
        return 'google_oauth';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function transformSingleItem(array $item, int $userId): ?CreateContactDTO
    {
        // Extract email from from_attendee or to_attendees
        $email = null;
        $name = null;

        // Try from_attendee first
        if (isset($item['from_attendee']['identifier'])) {
            $email = $item['from_attendee']['identifier'];
            $name = $item['from_attendee']['display_name'] ?? null;
        }
        
        // If no from_attendee, try first to_attendee
        if (!$email && isset($item['to_attendees'][0]['identifier'])) {
            $email = $item['to_attendees'][0]['identifier'];
            $name = $item['to_attendees'][0]['display_name'] ?? null;
        }

        if (!$email) {
            return null;
        }

        // Gmail-specific logic: extract name from email if no name provided
        if (!$name || $name === $email) {
            $name = $this->extractNameFromEmail($email);
        }

        $subject = $item['subject'] ?? '';
        $timestamp = $item['date'] ?? null;
        $messageCount = 1; // Gmail API doesn't provide thread length in this format

        $notes = sprintf(
            'Imported from Gmail | Subject: %s%s',
            $subject ? substr($subject, 0, 100) : 'No subject',
            $timestamp ? ' | Date: ' . $timestamp : ''
        );

        return CreateContactDTO::fromSyncData(
            userId: $userId,
            name: $name,
            provider: 'google_oauth',
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
