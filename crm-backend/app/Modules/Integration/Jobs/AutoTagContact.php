<?php

declare(strict_types=1);

namespace App\Modules\Integration\Jobs;

use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Services\ChatAnalysisService;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoTagContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $contactId
    ) {}

    public function handle(UnipileService $unipileService, ChatAnalysisService $chatAnalysisService, ContactRepositoryInterface $contactRepository): void
    {
        try {
            $contact = $contactRepository->findById($this->contactId);
            
            if (!$contact) {
                Log::warning('AutoTagContact: Contact not found', ['contact_id' => $this->contactId]);
                return;
            }

            // Skip if contact already has tags
            if (!empty($contact->tags)) {
                Log::debug('AutoTagContact: Contact already has tags, skipping', [
                    'contact_id' => $contact->id,
                    'existing_tags' => $contact->tags
                ]);
                return;
            }

            $contactIntegration = $contact->integrations()->first();

            if (!$contactIntegration) {
                Log::debug('AutoTagContact: No integration found for contact', [
                    'contact_id' => $contact->id
                ]);
                return;
            }

            $chatId = $contactIntegration->external_id;
            $accountId = $contactIntegration->integratedAccount->unipile_account_id;

            Log::info('AutoTagContact: Starting tagging process', [
                'contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'chat_id' => $chatId,
                'account_id' => $accountId,
            ]);

            // Try to get messages from the chat
            $messages = $unipileService->listChatMessages($accountId, $chatId, 1000);

            if (empty($messages['messages'])) {
                Log::info('AutoTagContact: No messages found, skipping tagging', [
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->name,
                ]);
                return;
            }

            Log::info('AutoTagContact: Found messages, analyzing', [
                'contact_id' => $contact->id,
                'message_count' => count($messages['messages']),
            ]);

            // Analyze messages and get best tag
            $bestTag = $chatAnalysisService->analyzeChatMessages($messages['messages']);

            if ($bestTag) {
                $contactRepository->update($contact, ['tags' => [$bestTag]]);
                
                Log::info('AutoTagContact: Successfully tagged contact', [
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->name,
                    'tag' => $bestTag,
                ]);
            } else {
                Log::info('AutoTagContact: No suitable tag found', [
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->name,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('AutoTagContact: Error processing contact', [
                'contact_id' => $this->contactId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
