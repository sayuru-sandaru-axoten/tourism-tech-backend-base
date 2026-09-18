<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Enums\ExperienceType;
use App\Domains\Experiences\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'destination_id' => Destination::factory(),
            'partner_id' => null,
            'experience_type' => fake()->randomElement(ExperienceType::cases())->value,
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'duration_days' => fake()->numberBetween(1, 10),
            'duration_nights' => fake()->numberBetween(0, 9),
            'min_group_size' => 1,
            'max_group_size' => fake()->numberBetween(2, 20),
            'base_price' => fake()->randomFloat(2, 20, 2000),
            'currency' => 'USD',
            'status' => ExperienceStatus::Draft->value,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => ExperienceStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(['status' => ExperienceStatus::Archived->value]);
    }
}
