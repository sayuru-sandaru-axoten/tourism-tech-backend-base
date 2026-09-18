<?php

namespace App\Domains\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // scoped to the authenticated user's own profile
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:255'],
            'passport_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'preferences' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * @return array{date_of_birth?: string|null, nationality?: string|null, passport_number?: string|null, emergency_contact_name?: string|null, emergency_contact_phone?: string|null, preferences?: array<string, mixed>|null}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{date_of_birth?: string|null, nationality?: string|null, passport_number?: string|null, emergency_contact_name?: string|null, emergency_contact_phone?: string|null, preferences?: array<string, mixed>|null} */
        return parent::validated($key, $default);
    }
}
