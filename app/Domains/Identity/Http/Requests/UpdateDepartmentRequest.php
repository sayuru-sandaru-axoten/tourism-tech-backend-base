<?php

namespace App\Domains\Identity\Http\Requests;

use App\Domains\Identity\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route already gated by permission:departments.manage
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Department $department */
        $department = $this->route('department');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{name?: string, description?: string|null, is_active?: bool}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{name?: string, description?: string|null, is_active?: bool} */
        return parent::validated($key, $default);
    }
}
