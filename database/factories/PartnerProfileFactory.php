<?php

namespace Database\Factories;

use App\Domains\Partners\Models\Partner;
use App\Domains\Partners\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerProfile>
 */
class PartnerProfileFactory extends Factory
{
    protected $model = PartnerProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'partner_id' => Partner::factory(),
            'job_title' => fake()->jobTitle(),
        ];
    }
}
