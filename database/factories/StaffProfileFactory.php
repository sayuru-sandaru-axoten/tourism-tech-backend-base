<?php

namespace Database\Factories;

use App\Domains\Identity\Models\Department;
use App\Domains\Identity\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffProfile>
 */
class StaffProfileFactory extends Factory
{
    protected $model = StaffProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'employee_code' => strtoupper(fake()->unique()->bothify('EMP-####')),
            'hire_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
        ];
    }
}
