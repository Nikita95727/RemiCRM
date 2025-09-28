<?php

declare(strict_types=1);

namespace App\Modules\Contact\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContactRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'sources' => [
                'nullable',
                'array',
            ],
            'sources.*' => [
                'string',
                Rule::in(['crm', 'telegram', 'whatsapp', 'gmail']),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Contact name is required.',
            'name.min' => 'Contact name must be at least 2 characters.',
            'name.max' => 'Contact name cannot exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'sources.*.in' => 'Invalid contact source provided.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        $validated['user_id'] = auth()->id();

        return $validated;
    }
}
