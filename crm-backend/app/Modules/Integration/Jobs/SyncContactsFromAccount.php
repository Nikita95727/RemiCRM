<?php

declare(strict_types=1);

namespace App\Modules\Integration\Jobs;

use App\Modules\Contact\DTOs\CreateContactDTO;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Exceptions\ContactSyncException;
use App\Modules\Integration\Exceptions\UnipileApiException;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use App\Modules\Integration\Transformers\ContactTransformerFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SyncContactsFromAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private IntegratedAccount $account
    ) {}

    public function handle(UnipileService $unipileService): void
    {
        try {
            if (! $this->account->isActive()) {
                Log::info('Skipping sync for inactive account', ['account_id' => $this->account->id]);
                return;
            }

            Log::info('✨ FIXED TRANSFORMER LOGIC STARTING', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
                'job_class' => self::class,
            ]);

            // Get raw data from Unipile API
            $rawData = $this->fetchRawDataFromProvider($unipileService);

            // Transform using provider-specific transformer
            $transformerFactory = new ContactTransformerFactory();
            $transformer = $transformerFactory->create($this->account->provider->value);
            
            Log::info('Using transformer for provider', [
                'provider' => $this->account->provider,
                'transformer_class' => get_class($transformer),
            ]);

            $contactDTOs = $transformer->transform($rawData, $this->account->user_id);

            // Save contacts to database
            $savedContacts = $this->saveContacts($contactDTOs);

            $this->account->update(['last_sync_at' => now()]);

            Log::info('🎉 FIXED TRANSFORMER - Provider-specific contact sync completed', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
                'contacts_transformed' => count($contactDTOs),
                'contacts_saved' => count($savedContacts),
            ]);

        } catch (UnipileApiException $e) {
            Log::error('Unipile API error during contact sync', array_merge(
                $e->getLogContext(),
                [
                    'account_id' => $this->account->id,
                    'provider' => $this->account->provider->value,
                ]
            ));

            $this->account->update([
                'status' => 'error',
                'error_message' => $e->getUserMessage(),
                'last_error_at' => now(),
            ]);

            throw $e;
        } catch (ContactSyncException $e) {
            Log::error('Contact sync error', array_merge(
                $e->getLogContext(),
                [
                    'account_id' => $this->account->id,
                    'provider' => $this->account->provider->value,
                ]
            ));

            $this->account->update([
                'status' => 'error',
                'error_message' => $e->getUserMessage(),
                'last_error_at' => now(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during contact sync', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider->value,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->account->update([
                'status' => 'error',
                'error_message' => 'An unexpected error occurred during contact synchronization.',
                'last_error_at' => now(),
            ]);

            throw new ContactSyncException(
                'Unexpected error during contact sync: ' . $e->getMessage(),
                'unexpected_error',
                ['account_id' => $this->account->id],
                'An unexpected error occurred during contact synchronization.',
                $e
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchRawDataFromProvider(UnipileService $unipileService): array
    {
        return match ($this->account->provider->value) {
            'telegram', 'whatsapp' => $unipileService->listAllChats($this->account->unipile_account_id),
            'gmail' => $unipileService->listEmails($this->account->unipile_account_id),
            default => [],
        };
    }

    /**
     * @param  CreateContactDTO[]  $contactDTOs
     * @return Contact[]
     */
    private function saveContacts(array $contactDTOs): array
    {
        $savedContacts = [];
        foreach ($contactDTOs as $contactDto) {
            try {
                // Check if a contact with the same provider_id already exists for this user
                $existingContact = Contact::where('user_id', $contactDto->userId)
                    ->whereJsonContains('sources', $this->account->provider)
                    ->whereHas('integrations', function ($query) use ($contactDto) {
                        $query->where('integrated_account_id', $this->account->id)
                            ->where('external_id', $contactDto->providerId);
                    })
                    ->first();

                if ($existingContact) {
                    Log::debug('Contact already exists, skipping creation', [
                        'contact_id' => $existingContact->id,
                        'name' => $existingContact->name,
                        'provider_id' => $contactDto->providerId,
                    ]);
                    $savedContacts[] = $existingContact;
                    continue;
                }

                $contact = Contact::create($contactDto->toArray());

                $contact->integrations()->attach($this->account->id, [
                    'external_id' => $contactDto->providerId,
                    'last_synced_at' => now(),
                ]);

                $savedContacts[] = $contact;

                Log::info('Contact created successfully', [
                    'contact_id' => $contact->id,
                    'name' => $contact->name,
                    'provider' => $this->account->provider,
                    'provider_id' => $contactDto->providerId,
                    'phone' => $contact->phone,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save contact from DTO', [
                    'dto' => $contactDto->toArray(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $savedContacts;
    }
}