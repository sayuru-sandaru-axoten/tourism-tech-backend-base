<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegionRequest extends FormRequest
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
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('regions', 'slug')->where(fn ($query) => $query->where('country_id', $this->input('country_id'))),
            ],
        ];
    }

    /**
     * @return array{country_id: int, name: string, slug?: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{country_id: int, name: string, slug?: string} */
        return parent::validated($key, $default);
    }
}
