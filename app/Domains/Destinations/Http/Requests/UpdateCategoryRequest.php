<?php

namespace App\Domains\Destinations\Http\Requests;

use App\Domains\Destinations\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'applies_to' => ['sometimes', Rule::in(['destination', 'experience'])],
        ];
    }

    /**
     * @return array{name?: string, slug?: string, applies_to?: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name?: string, slug?: string, applies_to?: string} */
        return parent::validated($key, $default);
    }
}
