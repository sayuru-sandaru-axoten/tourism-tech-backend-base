<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Enums\DestinationStatus;
use App\Domains\Destinations\Models\Destination;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class UpdateDestination implements Action
{
    /**
     * @param  array{region_id?: int, category_id?: int|null, name?: string, slug?: string, short_description?: string|null, description?: string|null, latitude?: float|null, longitude?: float|null, status?: string}  $data
     */
    public function handle(Destination $destination, array $data): Destination
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['status'])) {
            $newStatus = $data['status'];
            $wasPublished = $destination->status === DestinationStatus::Published;
            $willBePublished = $newStatus === DestinationStatus::Published->value;

            if ($willBePublished && ! $wasPublished) {
                $data['published_at'] = now();
            } elseif (! $willBePublished && $wasPublished) {
                $data['published_at'] = null;
            }
        }

        $destination->update($data);

        return $destination;
    }
}
