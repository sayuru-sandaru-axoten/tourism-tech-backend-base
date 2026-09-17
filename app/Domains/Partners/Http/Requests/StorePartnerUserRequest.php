<?php

namespace App\Domains\Partners\Http\Requests;

use App\Domains\Partners\Enums\PartnerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePartnerUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already gated by permission:partners.manage
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:filter', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12'],
            'job_title' => ['nullable', 'string', 'max:255'],
            // Either attach to an existing partner organisation, or create a new one inline.
            'partner_id' => ['required_without_all:partner_name,partner_type', 'nullable', 'integer', Rule::exists('partners', 'id')],
            'partner_name' => ['required_without:partner_id', 'nullable', 'string', 'max:255'],
            'partner_type' => ['required_without:partner_id', 'nullable', new Enum(PartnerType::class)],
        ];
    }

    /**
     * @return array{name: string, email: string, password: string, job_title?: string|null, partner_id?: int|null, partner_name?: string|null, partner_type?: string|null}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name: string, email: string, password: string, job_title?: string|null, partner_id?: int|null, partner_name?: string|null, partner_type?: string|null} */
        return parent::validated($key, $default);
    }
}
