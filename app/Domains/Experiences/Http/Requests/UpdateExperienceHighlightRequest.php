<?php

namespace App\Domains\Experiences\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienceHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already gated by permission:experiences.manage
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array{title?: string, description?: string|null, sort_order?: int}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{title?: string, description?: string|null, sort_order?: int} */
        return parent::validated($key, $default);
    }
}
