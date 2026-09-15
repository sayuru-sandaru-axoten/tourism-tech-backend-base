<?php

namespace Tests\Unit\Domains\Identity\Requests;

use App\Domains\Identity\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accepts_valid_registration_data(): void
    {
        $validator = Validator::make([
            'name' => 'Ama Perera',
            'email' => 'ama@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ], (new RegisterRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_a_short_password(): void
    {
        $validator = Validator::make([
            'name' => 'Ama Perera',
            'email' => 'ama@example.com',
            'password' => 'too-short',
            'password_confirmation' => 'too-short',
        ], (new RegisterRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_it_rejects_a_mismatched_password_confirmation(): void
    {
        $validator = Validator::make([
            'name' => 'Ama Perera',
            'email' => 'ama@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'does-not-match',
        ], (new RegisterRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_it_rejects_an_email_already_in_use(): void
    {
        $existing = User::factory()->create();

        $validator = Validator::make([
            'name' => 'Ama Perera',
            'email' => $existing->email,
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ], (new RegisterRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_it_rejects_missing_required_fields(): void
    {
        $validator = Validator::make([], (new RegisterRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertEqualsCanonicalizing(
            ['name', 'email', 'password'],
            array_keys($validator->errors()->toArray()),
        );
    }
}
