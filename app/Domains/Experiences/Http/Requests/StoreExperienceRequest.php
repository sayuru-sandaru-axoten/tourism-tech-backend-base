<?php

namespace App\Domains\Experiences\Http\Requests;

use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Enums\ExperienceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExperienceRequest extends FormRequest
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
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'experience_type' => ['required', Rule::enum(ExperienceType::class)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('experiences', 'slug')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:0'],
            'duration_nights' => ['nullable', 'integer', 'min:0'],
            'min_group_size' => ['nullable', 'integer', 'min:0'],
            'max_group_size' => ['nullable', 'integer', 'min:0'],
            'base_price' => ['nullable', 'numeric', 'min:0', 'required_with:currency'],
            'currency' => ['nullable', 'string', 'regex:/^[A-Z]{3}$/', 'required_with:base_price'],
            'status' => ['sometimes', Rule::enum(ExperienceStatus::class)],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => [Rule::exists('categories', 'id')->where('applies_to', 'experience')],
        ];
    }

    /**
     * @return array{destination_id: int, partner_id?: int|null, experience_type: string, title: string, slug?: string, short_description?: string|null, description?: string|null, duration_days?: int|null, duration_nights?: int|null, min_group_size?: int|null, max_group_size?: int|null, base_price?: float|null, currency?: string|null, status?: string, category_ids?: list<int>}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{destination_id: int, partner_id?: int|null, experience_type: string, title: string, slug?: string, short_description?: string|null, description?: string|null, duration_days?: int|null, duration_nights?: int|null, min_group_size?: int|null, max_group_size?: int|null, base_price?: float|null, currency?: string|null, status?: string, category_ids?: list<int>} */
        return parent::validated($key, $default);
    }
}
