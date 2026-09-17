<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMediaRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:media,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array{items: list<array{id: int, sort_order: int}>}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{items: list<array{id: int, sort_order: int}>} */
        return parent::validated($key, $default);
    }
}
