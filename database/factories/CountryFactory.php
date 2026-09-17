<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->country(),
            'iso_code' => fake()->unique()->countryCode(),
        ];
    }
}
