<?php

namespace Database\Factories;

use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\ExperienceHighlight;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExperienceHighlight>
 */
class ExperienceHighlightFactory extends Factory
{
    protected $model = ExperienceHighlight::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }
}
