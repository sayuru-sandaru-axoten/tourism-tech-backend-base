<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Region;
use App\Support\Contracts\Action;

class DeleteRegion implements Action
{
    public function handle(Region $region): void
    {
        $region->delete();
    }
}
