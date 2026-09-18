<?php

namespace Database\Factories;

use App\Domains\Identity\Models\TravelerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TravelerProfile>
 */
class TravelerProfileFactory extends Factory
{
    protected $model = TravelerProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'nationality' => fake()->countryCode(),
            'passport_number' => strtoupper(fake()->bothify('??#######')),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'preferences' => [],
        ];
    }
}
