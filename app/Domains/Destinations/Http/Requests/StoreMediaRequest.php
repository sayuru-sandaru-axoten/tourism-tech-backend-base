<?php

namespace App\Domains\Destinations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreMediaRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:10240'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array{file: UploadedFile, alt_text?: string|null, sort_order?: int}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{file: UploadedFile, alt_text?: string|null, sort_order?: int} */
        return parent::validated($key, $default);
    }
}
