<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConnectProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                'string',
                Rule::in(['telegram', 'whatsapp', 'gmail']),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider.required' => 'Provider is required.',
            'provider.in' => 'Provider must be one of: telegram, whatsapp, gmail.',
        ];
    }

    public function getProvider(): string
    {
        return $this->validated()['provider'];
    }
}
