<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlaceRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'place_type' => ['sometimes', 'string', Rule::in(['landmark', 'viewpoint', 'temple', 'beach', 'trail'])],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array{name?: string, place_type?: string, description?: string|null, latitude?: float|null, longitude?: float|null}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name?: string, place_type?: string, description?: string|null, latitude?: float|null, longitude?: float|null} */
        return parent::validated($key, $default);
    }
}
