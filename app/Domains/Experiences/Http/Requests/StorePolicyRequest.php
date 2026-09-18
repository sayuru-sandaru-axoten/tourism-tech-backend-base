<?php

namespace App\Domains\Experiences\Http\Requests;

use App\Domains\Experiences\Enums\PolicyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePolicyRequest extends FormRequest
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
            'policy_type' => ['required', Rule::enum(PolicyType::class)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }

    /**
     * @return array{policy_type: string, title: string, body: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{policy_type: string, title: string, body: string} */
        return parent::validated($key, $default);
    }
}
