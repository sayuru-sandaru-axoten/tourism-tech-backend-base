<?php

namespace App\Domains\Identity\Http\Resources;

use App\Domains\Identity\Models\TravelerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TravelerProfile
 */
class TravelerProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'nationality' => $this->nationality,
            'passport_number' => $this->passport_number,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'loyalty_tier' => $this->loyalty_tier,
            'loyalty_points' => $this->loyalty_points,
            'preferences' => $this->preferences,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
