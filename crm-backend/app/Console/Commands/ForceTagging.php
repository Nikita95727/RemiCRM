<?php

namespace App\Console\Commands;

use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Services\ChatAnalysisService;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ForceTagging extends Command
{
    protected $signature = 'force:tagging {contact_id? : Id of contact. If it is not entered then it works for all contacts}';

    protected $description = 'Launching the force tagging';

    public function handle(UnipileService $unipileService, ChatAnalysisService $chatAnalysisService): void
    {
        $contactId = $this->argument('contact_id');

        if ($contactId) {

            $this->processContact((int) $contactId, $unipileService, $chatAnalysisService);
        } else {

            $this->processAllContacts($unipileService, $chatAnalysisService);
        }
    }

    private function processContact(int $contactId, UnipileService $unipileService, ChatAnalysisService $chatAnalysisService): void
    {
        $contact = Contact::find($contactId);

        if (! $contact) {
            $this->error("Contact not found");

            return;
        }

        $contactIntegration = $contact->integrations()->first();

        if (! $contactIntegration) {
            $this->warn("Contact with {$contactId} lost connection with the integrations");
            return;
        }

        $this->applyTagging($contact, $contactIntegration, $unipileService, $chatAnalysisService);
    }

    private function processAllContacts(UnipileService $unipileService, ChatAnalysisService $chatAnalysisService): void
    {
        $contacts = Contact::whereHas('integrations')->get();
        $progressBar = $this->output->createProgressBar($contacts->count());
        $progressBar->start();

        $stats = [
            'processed' => 0,
            'tagged' => 0,
            'no_messages' => 0,
            'errors' => 0,
        ];

        foreach ($contacts as $contact) {
            $contactIntegration = $contact->integrations()->first();

            if ($contactIntegration) {
                $result = $this->applyTagging($contact, $contactIntegration, $unipileService, $chatAnalysisService);

                switch ($result) {
                    case 'tagged':
                        $stats['tagged']++;
                        break;
                    case 'no_messages':
                        $stats['no_messages']++;
                        break;
                    case 'error':
                        $stats['errors']++;
                        break;
                }
            }

            $stats['processed']++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function applyTagging(
        Contact $contact,
        $contactIntegration,
        UnipileService $unipileService,
        ChatAnalysisService $chatAnalysisService,
    ): string
    {
        $chatId = $contactIntegration->external_id;
        $accountId = $contactIntegration->integratedAccount->unipile_account_id;

        try {
            $messages = $unipileService->listChatMessages($accountId, $chatId, 1000);

            if (empty($messages['messages'])) {
                return 'no_messages';
            }

            $bestTag = $chatAnalysisService->analyzeChatMessages($messages['messages']);

            if ($bestTag) {
                $contact->update(['tags' => [$bestTag]]);
                return 'tagged';
            }

            return 'no_messages';

        } catch (\Exception $e) {
            Log::error('ForceTagging: Error processing contact', [
                'contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'chat_id' => $chatId,
                'account_id' => $accountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 'error';
        }
    }
}
