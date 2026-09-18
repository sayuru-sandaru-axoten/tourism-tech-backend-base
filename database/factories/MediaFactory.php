<?php

namespace Database\Factories;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mediable_type' => 'destination',
            'mediable_id' => Destination::factory(),
            'disk' => 'public',
            'path' => 'destinations-media/'.fake()->uuid().'.jpg',
            'media_type' => 'image',
            'alt_text' => fake()->sentence(4),
            'sort_order' => 0,
        ];
    }
}
