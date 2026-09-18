<?php

namespace App\Domains\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already gated by permission:users.manage
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
            // 'traveler' and 'partner-user' are assigned by their own registration
            // flows, never through staff account creation.
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'api'),
                Rule::notIn(['traveler', 'partner-user']),
            ],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'employee_code' => ['nullable', 'string', 'max:255', Rule::unique('staff_profiles', 'employee_code')],
            'hire_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array{name: string, email: string, password: string, role: string, department_id?: int|null, employee_code?: string|null, hire_date?: string|null}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name: string, email: string, password: string, role: string, department_id?: int|null, employee_code?: string|null, hire_date?: string|null} */
        return parent::validated($key, $default);
    }
}
