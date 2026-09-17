<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Country;
use App\Support\Contracts\Action;

class DeleteCountry implements Action
{
    public function handle(Country $country): void
    {
        $country->delete();
    }
}
