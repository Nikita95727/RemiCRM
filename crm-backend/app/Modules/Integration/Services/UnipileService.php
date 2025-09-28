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

        $this->dsn = $dsn ?? config('services.unipile.dsn', 'temp_dsn');
        $this->token = $token ?? config('services.unipile.token', 'temp_token');

        $this->baseUrl = $baseUrl ?: "https://api18.unipile.com:14862";
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
        $maxPages = 10;
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
    public function listEmails(string $accountId): array
    {
        try {
            $response = $this->makeRequest('GET', '/emails', [
                'account_id' => $accountId,
            ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Unipile API error in listEmails: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get messages from a specific chat
     *
     * @return array<string, mixed>
     */
    public function listChatMessages(string $accountId, string $chatId, int $limit = 1000): array
    {
        try {
            $endpoints = [
                "/messages",
                "/chats/{$chatId}/messages",
                "/messages/{$chatId}",
            ];

            foreach ($endpoints as $endpoint) {
                $params = [
                    'limit' => min($limit, 250),
                ];

                if ($endpoint === "/messages") {
                    $params['account_id'] = $accountId;
                    $params['chat_id'] = $chatId;
                } else {
                    $params['account_id'] = $accountId;
                }

                $actualEndpoint = str_replace('{$chatId}', $chatId, $endpoint);
                $response = $this->makeRequest('GET', $actualEndpoint, $params);

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
            }


            Log::warning('All endpoints failed to fetch chat messages', [
                'account_id' => $accountId,
                'chat_id' => $chatId,
                'tried_endpoints' => $endpoints,
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
            $data = [
                'type' => 'create',
                'providers' => $providers,
                'api_url' => $this->baseUrl,
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
        $url = $this->baseUrl.$endpoint;

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
