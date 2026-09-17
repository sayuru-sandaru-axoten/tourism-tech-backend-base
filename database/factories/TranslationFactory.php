<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translatable_type' => 'destination',
            'translatable_id' => Destination::factory(),
            'locale' => 'en',
            'field_key' => 'description',
            'value' => fake()->sentence(),
        ];
    }
}
