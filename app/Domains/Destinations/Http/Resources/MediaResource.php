<?php

namespace App\Domains\Destinations\Http\Resources;

use App\Domains\Destinations\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'mediable_type' => $this->mediable_type,
            'mediable_id' => $this->mediable_id,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $this->url,
            'media_type' => $this->media_type,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
