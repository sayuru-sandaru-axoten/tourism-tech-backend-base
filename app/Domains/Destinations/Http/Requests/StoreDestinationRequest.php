<?php

namespace App\Domains\Destinations\Http\Requests;

use App\Domains\Destinations\Enums\DestinationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDestinationRequest extends FormRequest
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
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('destinations', 'slug')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['sometimes', Rule::enum(DestinationStatus::class)],
        ];
    }

    /**
     * @return array{region_id: int, category_id?: int|null, name: string, slug?: string, short_description?: string|null, description?: string|null, latitude?: float|null, longitude?: float|null, status?: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{region_id: int, category_id?: int|null, name: string, slug?: string, short_description?: string|null, description?: string|null, latitude?: float|null, longitude?: float|null, status?: string} */
        return parent::validated($key, $default);
    }
}
