<?php

namespace App\Domains\Experiences\Http\Resources;

use App\Domains\Destinations\Http\Resources\CategoryResource;
use App\Domains\Destinations\Http\Resources\DestinationResource;
use App\Domains\Destinations\Http\Resources\MediaResource;
use App\Domains\Destinations\Http\Resources\TranslationResource;
use App\Domains\Experiences\Models\Experience;
use App\Domains\Partners\Http\Resources\PartnerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Experience
 */
class ExperienceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'destination_id' => $this->destination_id,
            'partner_id' => $this->partner_id,
            'experience_type' => $this->experience_type->value,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'duration_days' => $this->duration_days,
            'duration_nights' => $this->duration_nights,
            'min_group_size' => $this->min_group_size,
            'max_group_size' => $this->max_group_size,
            'base_price' => $this->base_price,
            'currency' => $this->currency,
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'destination' => new DestinationResource($this->whenLoaded('destination')),
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'highlights' => ExperienceHighlightResource::collection($this->whenLoaded('highlights')),
            'policies' => PolicyResource::collection($this->whenLoaded('policies')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
