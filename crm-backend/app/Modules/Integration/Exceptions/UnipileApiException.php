<?php

declare(strict_types=1);

namespace App\Modules\Integration\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class UnipileApiException extends Exception
{
    private int $statusCode;
    private array $errorData;
    private string $userMessage;

    public function __construct(
        string $message,
        int $statusCode,
        array $errorData = [],
        string $userMessage = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
        $this->userMessage = $userMessage ?: $this->generateUserMessage($statusCode, $errorData);
    }

    public static function fromResponse(Response $response, string $context = ''): self
    {
        $statusCode = $response->status();
        $errorData = $response->json() ?? [];
        
        $technicalMessage = sprintf(
            'Unipile API error in %s: HTTP %d - %s',
            $context,
            $statusCode,
            $errorData['title'] ?? $errorData['message'] ?? 'Unknown error'
        );

        return new self(
            $technicalMessage,
            $statusCode,
            $errorData
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    private function generateUserMessage(int $statusCode, array $errorData): string
    {
        return match ($statusCode) {
            400 => $this->handleBadRequest($errorData),
            401 => $this->handleUnauthorized($errorData),
            403 => $this->handleForbidden($errorData),
            404 => $this->handleNotFound($errorData),
            422 => $this->handleValidationError($errorData),
            429 => 'Too many requests. Please try again later.',
            500, 502, 503, 504 => 'Temporary service issues. Please try again later.',
            default => 'Connection error occurred. Please contact administrator.',
        };
    }

    private function handleBadRequest(array $errorData): string
    {
        $message = $errorData['message'] ?? $errorData['title'] ?? '';
        
        if (str_contains(strtolower($message), 'credentials')) {
            return 'Invalid credentials. Please check your login details.';
        }
        
        if (str_contains(strtolower($message), 'phone')) {
            return 'Invalid phone number format. Please use international format (+7...).';
        }
        
        if (str_contains(strtolower($message), 'code')) {
            return 'Invalid verification code. Please check the code and try again.';
        }
        
        return 'Invalid request data. Please check the information provided.';
    }

    private function handleUnauthorized(array $errorData): string
    {
        $message = $errorData['message'] ?? $errorData['title'] ?? '';
        
        if (str_contains(strtolower($message), 'token') || str_contains(strtolower($message), 'api')) {
            return 'API key issue. Please contact administrator.';
        }
        
        if (str_contains(strtolower($message), '2fa') || str_contains(strtolower($message), 'two-factor')) {
            return 'Two-factor authentication required. Please enter the code from your authenticator app.';
        }
        
        if (str_contains(strtolower($message), 'session')) {
            return 'Session expired. Please try connecting your account again.';
        }
        
        return 'Authorization error. Please check your credentials and try again.';
    }

    private function handleForbidden(array $errorData): string
    {
        return 'Access denied. The account may be blocked or lacks necessary permissions.';
    }

    private function handleNotFound(array $errorData): string
    {
        return 'Requested resource not found. The account may have been deleted.';
    }

    private function handleValidationError(array $errorData): string
    {
        $message = $errorData['message'] ?? $errorData['title'] ?? '';
        
        if (str_contains(strtolower($message), 'phone')) {
            return 'Invalid phone number format.';
        }
        
        if (str_contains(strtolower($message), 'email')) {
            return 'Invalid email address format.';
        }
        
        return 'Data validation failed. Please correct the errors and try again.';
    }

    public function getLogContext(): array
    {
        return [
            'status_code' => $this->statusCode,
            'error_data' => $this->errorData,
            'user_message' => $this->userMessage,
            'trace' => $this->getTraceAsString(),
        ];
    }
}
