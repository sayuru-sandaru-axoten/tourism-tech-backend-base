<?php

namespace App\Domains\Partners\Http\Resources;

use App\Domains\Identity\Http\Resources\UserResource;
use App\Domains\Partners\Models\PartnerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PartnerProfile
 */
class PartnerProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'user' => new UserResource($this->whenLoaded('user')),
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            'job_title' => $this->job_title,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
