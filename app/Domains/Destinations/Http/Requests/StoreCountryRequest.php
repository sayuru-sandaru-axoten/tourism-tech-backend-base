<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCountryRequest extends FormRequest
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
            'iso_code' => ['required', 'string', 'size:2', Rule::unique('countries', 'iso_code')],
        ];
    }

    /**
     * @return array{name: string, iso_code: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name: string, iso_code: string} */
        return parent::validated($key, $default);
    }
}
