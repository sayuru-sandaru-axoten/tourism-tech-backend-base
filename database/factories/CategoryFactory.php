<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Beach', 'Wildlife', 'Historical', 'Adventure', 'Camping', 'Hiking',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'applies_to' => 'destination',
        ];
    }

    public function forExperience(): static
    {
        return $this->state(['applies_to' => 'experience']);
    }
}
