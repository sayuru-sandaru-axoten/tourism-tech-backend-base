<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Place;
use App\Support\Contracts\Action;

class DeletePlace implements Action
{
    public function handle(Place $place): void
    {
        $place->delete();
    }
}
