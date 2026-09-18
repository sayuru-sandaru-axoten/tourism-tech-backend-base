<?php

namespace App\Domains\Destinations\Actions;

use App\Domains\Destinations\Models\Region;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class CreateRegion implements Action
{
    /**
     * @param  array{country_id: int, name: string, slug?: string}  $data
     */
    public function handle(array $data): Region
    {
        return Region::create([
            'country_id' => $data['country_id'],
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
        ]);
    }
}
