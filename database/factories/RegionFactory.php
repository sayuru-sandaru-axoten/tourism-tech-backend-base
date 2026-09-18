<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Country;
use App\Domains\Destinations\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'country_id' => Country::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
