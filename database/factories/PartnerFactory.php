<?php

namespace Database\Factories;

use App\Domains\Partners\Enums\PartnerType;
use App\Domains\Partners\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'partner_type' => fake()->randomElement(PartnerType::cases())->value,
        ];
    }
}
