<?php

namespace App\Domains\Identity\Http\Requests;

use App\Domains\Identity\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ChangeUserStatusRequest extends FormRequest
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
            'status' => ['required', new Enum(UserStatus::class)],
        ];
    }

    public function status(): UserStatus
    {
        return UserStatus::from($this->string('status')->toString());
    }
}
