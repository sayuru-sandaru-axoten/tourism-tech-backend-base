<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'slug')],
            'applies_to' => ['required', Rule::in(['destination', 'experience'])],
        ];
    }

    /**
     * @return array{name: string, slug?: string, applies_to: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name: string, slug?: string, applies_to: string} */
        return parent::validated($key, $default);
    }
}
