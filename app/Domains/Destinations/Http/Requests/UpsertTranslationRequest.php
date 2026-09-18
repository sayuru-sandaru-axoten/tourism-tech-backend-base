<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already gated by permission:destinations.manage
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/'],
            'field_key' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string'],
        ];
    }

    /**
     * @return array{locale: string, field_key: string, value: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{locale: string, field_key: string, value: string} */
        return parent::validated($key, $default);
    }
}
