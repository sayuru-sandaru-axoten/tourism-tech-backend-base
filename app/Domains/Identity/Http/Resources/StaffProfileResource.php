<?php

namespace App\Domains\Identity\Http\Resources;

use App\Domains\Identity\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StaffProfile
 */
class StaffProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'user' => new UserResource($this->whenLoaded('user')),
            'department_id' => $this->department_id,
            'employee_code' => $this->employee_code,
            'hire_date' => $this->hire_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
