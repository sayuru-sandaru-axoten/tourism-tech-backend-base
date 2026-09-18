<?php

namespace App\Domains\Destinations\Http\Requests;

use App\Domains\Destinations\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
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
        /** @var Country $country */
        $country = $this->route('country');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'iso_code' => ['sometimes', 'string', 'size:2', Rule::unique('countries', 'iso_code')->ignore($country->id)],
        ];
    }

    /**
     * @return array{name?: string, iso_code?: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name?: string, iso_code?: string} */
        return parent::validated($key, $default);
    }
}
