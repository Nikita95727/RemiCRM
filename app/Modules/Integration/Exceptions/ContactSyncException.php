<?php

declare(strict_types=1);

namespace App\Modules\Integration\Exceptions;

use Exception;

class ContactSyncException extends Exception
{
    private string $syncStage;
    private array $context;
    private string $userMessage;

    public function __construct(
        string $message,
        string $syncStage,
        array $context = [],
        string $userMessage = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->syncStage = $syncStage;
        $this->context = $context;
        $this->userMessage = $userMessage ?: $this->generateUserMessage($syncStage, $message);
    }

    public static function accountNotFound(string $accountId): self
    {
        return new self(
            "Integrated account not found: {$accountId}",
            'account_lookup',
            ['account_id' => $accountId],
            'Sync account not found. Please try reconnecting your account.'
        );
    }

    public static function apiConnectionFailed(string $accountId, \Throwable $previous): self
    {
        return new self(
            "Failed to connect to API for account: {$accountId}",
            'api_connection',
            ['account_id' => $accountId],
            'Failed to connect to service. Please check your internet connection.',
            $previous
        );
    }

    public static function chatsFetchFailed(string $accountId, \Throwable $previous): self
    {
        return new self(
            "Failed to fetch chats for account: {$accountId}",
            'chats_fetch',
            ['account_id' => $accountId],
            'Error fetching chat list. Please try again later.',
            $previous
        );
    }

    public static function contactCreationFailed(array $contactData, \Throwable $previous): self
    {
        return new self(
            "Failed to create contact: " . ($contactData['name'] ?? 'unknown'),
            'contact_creation',
            ['contact_data' => $contactData],
            'Error creating contact. Some contacts may not have been saved.',
            $previous
        );
    }

    public static function taggingFailed(int $contactId, \Throwable $previous): self
    {
        return new self(
            "Failed to apply automatic tagging for contact: {$contactId}",
            'auto_tagging',
            ['contact_id' => $contactId],
            'Error applying automatic tags. Contact created but tags not assigned.',
            $previous
        );
    }

    public static function messagesFetchFailed(string $chatId, \Throwable $previous): self
    {
        return new self(
            "Failed to fetch messages for chat: {$chatId}",
            'messages_fetch',
            ['chat_id' => $chatId],
            'Error fetching chat messages for analysis.',
            $previous
        );
    }

    public static function pythonAnalysisFailed(string $chatId, \Throwable $previous): self
    {
        return new self(
            "Python analysis script failed for chat: {$chatId}",
            'python_analysis',
            ['chat_id' => $chatId],
            'Error analyzing chat content for tag assignment.',
            $previous
        );
    }

    public function getSyncStage(): string
    {
        return $this->syncStage;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    private function generateUserMessage(string $syncStage, string $technicalMessage): string
    {
        return match ($syncStage) {
            'account_lookup' => 'Sync account not found.',
            'api_connection' => 'Service connection issues.',
            'chats_fetch' => 'Error fetching chat list.',
            'contact_creation' => 'Error creating contacts.',
            'auto_tagging' => 'Error assigning tags.',
            'messages_fetch' => 'Error fetching messages.',
            'python_analysis' => 'Error analyzing chats.',
            default => 'Contact synchronization error occurred.',
        };
    }

    public function getLogContext(): array
    {
        return [
            'sync_stage' => $this->syncStage,
            'context' => $this->context,
            'user_message' => $this->userMessage,
            'trace' => $this->getTraceAsString(),
        ];
    }
}
