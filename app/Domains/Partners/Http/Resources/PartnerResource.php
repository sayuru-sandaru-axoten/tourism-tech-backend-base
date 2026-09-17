<?php

namespace App\Domains\Partners\Http\Resources;

use App\Domains\Partners\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Partner
 */
class PartnerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'partner_type' => $this->partner_type->value,
        ];
    }
}
