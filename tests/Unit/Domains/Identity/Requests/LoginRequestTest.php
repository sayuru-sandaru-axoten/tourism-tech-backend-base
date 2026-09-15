<?php

namespace Tests\Unit\Domains\Identity\Requests;

use App\Domains\Identity\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    public function test_it_accepts_valid_credentials(): void
    {
        $validator = Validator::make([
            'email' => 'ama@example.com',
            'password' => 'correct-horse-battery-staple',
        ], (new LoginRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_missing_credentials(): void
    {
        $validator = Validator::make([], (new LoginRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertEqualsCanonicalizing(
            ['email', 'password'],
            array_keys($validator->errors()->toArray()),
        );
    }
}
