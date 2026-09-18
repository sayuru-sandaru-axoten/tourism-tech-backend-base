<?php

namespace Database\Factories;

use App\Domains\Destinations\Enums\DestinationStatus;
use App\Domains\Destinations\Models\Category;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    protected $model = Destination::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city().' '.fake()->randomElement(['Getaway', 'Escape', 'Retreat']);

        return [
            'region_id' => Region::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'status' => DestinationStatus::Draft->value,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => DestinationStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(['status' => DestinationStatus::Archived->value]);
    }
}
