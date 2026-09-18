<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Country;
use App\Support\Contracts\Action;

class CreateCountry implements Action
{
    /**
     * @param  array{name: string, iso_code: string}  $data
     */
    public function handle(array $data): Country
    {
        return Country::create($data);
    }
}
