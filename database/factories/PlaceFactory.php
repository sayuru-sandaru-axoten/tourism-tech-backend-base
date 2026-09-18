<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
class PlaceFactory extends Factory
{
    protected $model = Place::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'destination_id' => Destination::factory(),
            'name' => fake()->streetName(),
            'place_type' => fake()->randomElement(['landmark', 'viewpoint', 'temple', 'beach', 'trail']),
            'description' => fake()->sentence(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
