<?php

declare(strict_types=1);

namespace App\Modules\Integration\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UnipileDataValidator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function validateAccountData(array $data): array
    {
        $validator = Validator::make($data, [
            'id' => 'required|string',
            'type' => 'required|string|in:TELEGRAM,WHATSAPP,GMAIL',
            'name' => 'required|string|max:255',
            'created_at' => 'sometimes|string',
            'sources' => 'sometimes|array',
            'sources.*.id' => 'sometimes|string',
            'sources.*.status' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function validateChatData(array $data): array
    {
        $validator = Validator::make($data, [
            'attendee_provider_id' => 'required|string',
            'name' => 'required|string|max:255',
            'type' => 'sometimes|integer', // Unipile returns integer type
            'last_message_date' => 'sometimes|string',
            'message_count' => 'sometimes|integer|min:0',
            'timestamp' => 'sometimes|string', // Add timestamp validation
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function validateEmailData(array $data): array
    {
        $validator = Validator::make($data, [
            'from.email' => 'required|email',
            'from.name' => 'sometimes|string|max:255',
            'to' => 'sometimes|array',
            'to.*.email' => 'sometimes|email',
            'to.*.name' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string',
            'date' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
