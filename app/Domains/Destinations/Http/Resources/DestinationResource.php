<?php

namespace App\Domains\Destinations\Http\Resources;

use App\Domains\Destinations\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Destination
 */
class DestinationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'region_id' => $this->region_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'region' => new RegionResource($this->whenLoaded('region')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'places' => PlaceResource::collection($this->whenLoaded('places')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
