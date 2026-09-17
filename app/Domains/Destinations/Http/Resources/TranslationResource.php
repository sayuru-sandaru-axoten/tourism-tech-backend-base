<?php

namespace App\Domains\Destinations\Http\Resources;

use App\Domains\Destinations\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Translation
 */
class TranslationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'translatable_type' => $this->translatable_type,
            'translatable_id' => $this->translatable_id,
            'locale' => $this->locale,
            'field_key' => $this->field_key,
            'value' => $this->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
