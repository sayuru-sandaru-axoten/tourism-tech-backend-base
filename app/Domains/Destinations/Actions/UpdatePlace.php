<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Place;
use App\Support\Contracts\Action;

class UpdatePlace implements Action
{
    /**
     * @param  array{name?: string, place_type?: string, description?: string|null, latitude?: float|null, longitude?: float|null}  $data
     */
    public function handle(Place $place, array $data): Place
    {
        $place->update($data);

        return $place;
    }
}
