<?php

namespace App\Domains\Destinations\Http\Resources;

use App\Domains\Destinations\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Region
 */
class RegionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'country_id' => $this->country_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country' => new CountryResource($this->whenLoaded('country')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
