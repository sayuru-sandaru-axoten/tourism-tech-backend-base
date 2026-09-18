<?php

namespace Database\Factories;

use App\Domains\Experiences\Enums\PolicyType;
use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Policy>
 */
class PolicyFactory extends Factory
{
    protected $model = Policy::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'policyable_type' => 'experience',
            'policyable_id' => Experience::factory(),
            'policy_type' => fake()->randomElement(PolicyType::cases())->value,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
        ];
    }
}
