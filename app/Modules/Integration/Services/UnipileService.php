<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Modules\Integration\Exceptions\UnipileApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnipileService
{
    private string $dsn;

    private string $token;

    private string $baseUrl;

    public function __construct(?string $dsn = null, ?string $token = null, ?string $baseUrl = null)
    {

        $this->dsn = $dsn ?? config('services.unipile.dsn', 'api14.unipile.com:14426');
        $this->token = $token ?? config('services.unipile.token', 'gHer45qA.4OT9lImpNx1R0ryGFe3xgYYozGE475b3uSJTaMUJlsU=');

        $this->baseUrl = $baseUrl ?: (config('services.unipile.base_url') ?? 'api14.unipile.com:14426');
    }

    /**
     * @return array<string, mixed>
     */
    public function listAccounts(): array
    {
        try {
            $response = $this->makeRequest('GET', '/accounts');

            if (! $response->successful()) {
                $exception = UnipileApiException::fromResponse($response, 'listAccounts');
                Log::error('Unipile API error in listAccounts', $exception->getLogContext());
                throw $exception;
            }

            return $response->json() ?? [];
        } catch (UnipileApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in listAccounts', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new UnipileApiException(
                'Unexpected error in listAccounts: ' . $e->getMessage(),
                500,
                [],
                'An unexpected error occurred while fetching accounts.',
                $e
            );
        }
    }

    /**
     * @param array<string, mixed> $credentials
     * @return array<string, mixed>
     */
    public function connectAccount(string $provider, array $credentials): array
    {
        try {
            $response = $this->makeRequest('POST', '/accounts', [
                'provider' => $provider,
                'credentials' => $credentials,
            ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in connectAccount: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getAccount(string $accountId): array
    {
        try {
            $response = $this->makeRequest('GET', "/accounts/{$accountId}");

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in getAccount: ' . $e->getMessage());

            return [];
        }
    }

    public function deleteAccount(string $accountId): bool
    {
        try {
            $response = $this->makeRequest('DELETE', "/accounts/{$accountId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Unipile API error in deleteAccount: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function listChats(string $accountId, int $limit = 100, ?string $cursor = null): array
    {
        try {
            $params = [
                'account_id' => $accountId,
                'limit' => $limit,
                'sort' => '-last_message_date',
            ];

            if ($cursor) {
                $params['cursor'] = $cursor;
            }

            $response = $this->makeRequest('GET', '/chats', $params);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in listChats: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function listAllChats(string $accountId): array
    {
        $allChats = [];
        $cursor = null;
        $maxPages = 20; // Увеличиваем лимит для получения большего количества чатов
        $currentPage = 0;

        do {
            $currentPage++;
            $response = $this->listChats($accountId, 100, $cursor);

            if (empty($response['items'])) {
                break;
            }

            $allChats = array_merge($allChats, $response['items']);
            $cursor = $response['cursor'] ?? null;


        } while ($cursor && $currentPage < $maxPages);

        return [
            'items' => $allChats,
            'total' => count($allChats),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listAttendees(string $accountId): array
    {
        try {
            $response = $this->makeRequest('GET', '/attendees', [
                'account_id' => $accountId,
            ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in listAttendees: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfile(string $accountId, string $profileId): array
    {
        try {
            $response = $this->makeRequest('GET', "/profiles/{$profileId}", [
                'account_id' => $accountId,
            ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in getProfile: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function listEmails(string $accountId, int $limit = 100, ?string $cursor = null): array
    {
        try {
            $params = [
                'account_id' => $accountId,
                'limit' => $limit,
                'sort' => '-date',
            ];

            if ($cursor) {
                $params['cursor'] = $cursor;
            }

            $response = $this->makeRequest('GET', '/emails', $params);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in listEmails: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get all emails with pagination (like listAllChats)
     * @return array<string, mixed>
     */
    public function listAllEmails(string $accountId): array
    {
        $allEmails = [];
        $cursor = null;
        $maxPages = 20; // Увеличиваем лимит для email (больше писем чем чатов)
        $currentPage = 0;

        do {
            $currentPage++;
            $response = $this->listEmails($accountId, 100, $cursor);

            if (empty($response['items'])) {
                break;
            }

            $allEmails = array_merge($allEmails, $response['items']);
            $cursor = $response['cursor'] ?? null;

        } while ($cursor && $currentPage < $maxPages);

        return [
            'items' => $allEmails,
            'total' => count($allEmails),
        ];
    }

    /**
     * Stream emails with callback processing to avoid memory issues
     * @param string $accountId
     * @param callable $callback Function to process each batch: fn(array $items, int $page, ?string $cursor) => bool
     * @param int $batchSize Items per batch
     * @param int $maxPages Maximum pages to process
     * @return array Summary statistics
     */
    public function streamEmails(string $accountId, callable $callback, int $batchSize = 50, int $maxPages = 20): array
    {
        $cursor = null;
        $currentPage = 0;
        $totalProcessed = 0;
        $errors = [];

        do {
            $currentPage++;

            try {
                $response = $this->listEmails($accountId, $batchSize, $cursor);

                if (empty($response['items'])) {
                    break;
                }

                // Process batch with callback
                $shouldContinue = $callback($response['items'], $currentPage, $cursor);

                $totalProcessed += count($response['items']);
                $cursor = $response['cursor'] ?? null;

                // Allow callback to stop processing
                if ($shouldContinue === false) {
                    break;
                }

                // Memory cleanup
                unset($response);

                // Small delay to prevent API rate limiting
                if ($currentPage % 5 === 0) {
                    usleep(100000); // 100ms pause every 5 pages
                }

            } catch (\Exception $e) {
                $errors[] = [
                    'page' => $currentPage,
                    'error' => $e->getMessage(),
                    'cursor' => $cursor
                ];

                // Continue with next page on error
                $cursor = null;
            }

        } while ($cursor && $currentPage < $maxPages);

        return [
            'pages_processed' => $currentPage,
            'total_items' => $totalProcessed,
            'errors' => $errors,
            'completed' => empty($cursor) || $currentPage >= $maxPages
        ];
    }

    /**
     * Stream chats with callback processing to avoid memory issues
     * @param string $accountId
     * @param callable $callback Function to process each batch: fn(array $items, int $page, ?string $cursor) => bool
     * @param int $batchSize Items per batch
     * @param int $maxPages Maximum pages to process
     * @return array Summary statistics
     */
    public function streamChats(string $accountId, callable $callback, int $batchSize = 50, int $maxPages = 20): array
    {
        $cursor = null;
        $currentPage = 0;
        $totalProcessed = 0;
        $errors = [];

        do {
            $currentPage++;

            try {
                $response = $this->listChats($accountId, $batchSize, $cursor);

                if (empty($response['items'])) {
                    break;
                }

                // Process batch with callback
                $shouldContinue = $callback($response['items'], $currentPage, $cursor);

                $totalProcessed += count($response['items']);
                $cursor = $response['cursor'] ?? null;

                // Allow callback to stop processing
                if ($shouldContinue === false) {
                    break;
                }

                // Memory cleanup
                unset($response);

                // Small delay to prevent API rate limiting
                if ($currentPage % 5 === 0) {
                    usleep(100000); // 100ms pause every 5 pages
                }

            } catch (\Exception $e) {
                $errors[] = [
                    'page' => $currentPage,
                    'error' => $e->getMessage(),
                    'cursor' => $cursor
                ];

                // Continue with next page on error
                $cursor = null;
            }

        } while ($cursor && $currentPage < $maxPages);

        return [
            'pages_processed' => $currentPage,
            'total_items' => $totalProcessed,
            'errors' => $errors,
            'completed' => empty($cursor) || $currentPage >= $maxPages
        ];
    }

    /**
     * Get messages from a specific chat
     *
     * @return array<string, mixed>
     */
    /**
     * Get all chat messages with pagination for memory efficiency
     * @param int $maxMessages Maximum total messages to retrieve (default: 2000)
     * @param int $batchSize Messages per request (default: 250)
     */
    public function getAllChatMessages(string $accountId, string $chatId, int $maxMessages = 500, int $batchSize = 100): array
    {
        $allMessages = [];
        $cursor = null;
        $totalRetrieved = 0;
        $batchCount = 0;
        $maxBatches = 10; // Prevent infinite loops and reduce memory usage

        Log::info('Starting paginated message retrieval', [
            'account_id' => $accountId,
            'chat_id' => $chatId,
            'max_messages' => $maxMessages,
            'batch_size' => $batchSize,
        ]);

        do {
            $batchCount++;
            if ($batchCount > $maxBatches) {
                Log::warning('Reached maximum batch limit for message retrieval', [
                    'account_id' => $accountId,
                    'chat_id' => $chatId,
                    'batches_processed' => $batchCount,
                ]);
                break;
            }

            // Get next batch
            $remainingMessages = min($batchSize, $maxMessages - $totalRetrieved);
            if ($remainingMessages <= 0) {
                break;
            }

            $batchResult = $this->listChatMessages($accountId, $chatId, $remainingMessages);
            $batchMessages = $batchResult['messages'] ?? [];

            if (empty($batchMessages)) {
                // No more messages available
                break;
            }

            // Add to collection (memory efficient)
            $allMessages = array_merge($allMessages, $batchMessages);
            $totalRetrieved += count($batchMessages);
            $cursor = $batchResult['cursor'];

            Log::debug('Retrieved message batch', [
                'account_id' => $accountId,
                'chat_id' => $chatId,
                'batch' => $batchCount,
                'batch_size' => count($batchMessages),
                'total_retrieved' => $totalRetrieved,
                'has_cursor' => !empty($cursor),
            ]);

            // Stop if we've reached the limit
            if ($totalRetrieved >= $maxMessages) {
                break;
            }

            // Small delay to be respectful to API
            if ($batchCount % 5 === 0) {
                usleep(100000); // 100ms every 5 batches
            }

        } while ($cursor && $totalRetrieved < $maxMessages);

        Log::info('Completed paginated message retrieval', [
            'account_id' => $accountId,
            'chat_id' => $chatId,
            'total_messages' => count($allMessages),
            'batches_processed' => $batchCount,
            'max_reached' => $totalRetrieved >= $maxMessages,
        ]);

        return [
            'messages' => $allMessages,
            'total' => count($allMessages),
            'batches_used' => $batchCount,
        ];
    }

    public function listChatMessages(string $accountId, string $chatId, int $limit = 1000): array
    {
        try {
            // CORRECT endpoint format: /chats/{chatId}/messages WITHOUT account_id
            // Note: /api/v1/ is already added in makeRequest()
            $endpoint = "/chats/{$chatId}/messages";
            $params = [
                'limit' => min($limit, 250),
            ];

            $response = $this->makeRequest('GET', $endpoint, $params);

            if ($response->successful()) {
                $data = $response->json() ?? [];
                $messages = [];

                if (isset($data['messages']) && is_array($data['messages'])) {
                    $messages = $data['messages'];
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    $messages = $data['items'];
                } elseif (is_array($data) && isset($data[0]['object']) && $data[0]['object'] === 'Message') {
                    $messages = $data;
                }

                return [
                    'messages' => $messages,
                    'total' => $data['total'] ?? count($messages),
                    'cursor' => $data['cursor'] ?? null,
                ];
            }

            Log::warning('Failed to fetch chat messages', [
                'account_id' => $accountId,
                'chat_id' => $chatId,
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('Unipile API error in listChatMessages: ' . $e->getMessage(), [
                'account_id' => $accountId,
                'chat_id' => $chatId,
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }



    public function resyncAccount(string $accountId): bool
    {
        try {
            $response = $this->makeRequest('GET', "/accounts/{$accountId}/resync");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Unipile API error in resyncAccount: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @param array<string> $providers
     * @return array<string, mixed>
     */
    public function createHostedAuthLink(array $providers, string $userId, ?string $notifyUrl, ?string $redirectUrl = null): array
    {
        try {
            $apiUrl = str_starts_with($this->baseUrl, 'http') ? $this->baseUrl : 'https://' . $this->baseUrl;
            
            $data = [
                'type' => 'create',
                'providers' => array_map('strtoupper', $providers), // API expects uppercase
                'api_url' => $apiUrl,
                'expiresOn' => now()->addHours(2)->format('Y-m-d\TH:i:s.v\Z'),
                'name' => $userId,
            ];

            if ($notifyUrl) {
                $data['notify_url'] = $notifyUrl;
            }

            if ($redirectUrl) {
                $data['success_redirect_url'] = $redirectUrl;
            }

            $response = $this->makeRequest('POST', '/hosted/accounts/link', $data);

            if (! $response->successful()) {
                $exception = UnipileApiException::fromResponse($response, 'createHostedAuthLink');
                Log::error('Unipile API error in createHostedAuthLink', array_merge(
                    $exception->getLogContext(),
                    ['providers' => $providers, 'user_id' => $userId]
                ));
                throw $exception;
            }

            return $response->json() ?? [];
        } catch (UnipileApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in createHostedAuthLink', [
                'message' => $e->getMessage(),
                'providers' => $providers,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new UnipileApiException(
                'Unexpected error in createHostedAuthLink: ' . $e->getMessage(),
                500,
                [],
                'An unexpected error occurred while creating connection link.',
                $e
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $baseUrl = str_starts_with($this->baseUrl, 'http') ? $this->baseUrl : 'https://' . $this->baseUrl;
        $url = $baseUrl.'/api/v1'.$endpoint;

        $request = Http::withHeaders([
            'X-DSN' => $this->dsn,
            'X-API-KEY' => $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        return match (strtoupper($method)) {
            'GET' => $request->get($url, $data),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'PATCH' => $request->patch($url, $data),
            'DELETE' => $request->delete($url, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }
}
