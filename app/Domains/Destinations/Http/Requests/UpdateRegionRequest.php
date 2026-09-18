<?php

namespace App\Domains\Destinations\Http\Requests;

use App\Domains\Destinations\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegionRequest extends FormRequest
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
        /** @var Region $region */
        $region = $this->route('region');

        $countryId = $this->input('country_id', $region->country_id);

        return [
            'country_id' => ['sometimes', 'integer', 'exists:countries,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('regions', 'slug')
                    ->where(fn ($query) => $query->where('country_id', $countryId))
                    ->ignore($region->id),
            ],
        ];
    }

    /**
     * @return array{country_id?: int, name?: string, slug?: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{country_id?: int, name?: string, slug?: string} */
        return parent::validated($key, $default);
    }
}
