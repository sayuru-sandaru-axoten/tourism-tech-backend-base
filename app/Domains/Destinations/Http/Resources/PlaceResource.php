<?php

namespace App\Domains\Destinations\Http\Resources;

use App\Domains\Destinations\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Place
 */
class PlaceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'destination_id' => $this->destination_id,
            'name' => $this->name,
            'place_type' => $this->place_type,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
