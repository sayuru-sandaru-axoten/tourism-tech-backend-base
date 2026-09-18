<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Country;
use App\Support\Contracts\Action;

class UpdateCountry implements Action
{
    /**
     * @param  array{name?: string, iso_code?: string}  $data
     */
    public function handle(Country $country, array $data): Country
    {
        $country->update($data);

        return $country;
    }
}
