<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Region;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class UpdateRegion implements Action
{
    /**
     * @param  array{country_id?: int, name?: string, slug?: string}  $data
     */
    public function handle(Region $region, array $data): Region
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $region->update($data);

        return $region;
    }
}
