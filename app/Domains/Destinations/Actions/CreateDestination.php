<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Enums\DestinationStatus;
use App\Domains\Destinations\Models\Destination;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class CreateDestination implements Action
{
    /**
     * @param  array{region_id: int, category_id?: int|null, name: string, slug?: string, short_description?: string|null, description?: string|null, latitude?: float|null, longitude?: float|null, status?: string}  $data
     */
    public function handle(array $data): Destination
    {
        $status = $data['status'] ?? DestinationStatus::Draft->value;

        return Destination::create([
            'region_id' => $data['region_id'],
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => $status,
            'published_at' => $status === DestinationStatus::Published->value ? now() : null,
        ]);
    }
}
