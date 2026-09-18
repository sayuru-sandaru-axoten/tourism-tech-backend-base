<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Support\Contracts\Action;

class CreatePlace implements Action
{
    /**
     * @param  array{name: string, place_type: string, description?: string|null, latitude?: float|null, longitude?: float|null}  $data
     */
    public function handle(Destination $destination, array $data): Place
    {
        return $destination->places()->create($data);
    }
}
