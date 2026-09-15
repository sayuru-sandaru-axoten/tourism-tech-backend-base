<?php

namespace Tests\Unit\Domains\Identity\Actions;

use App\Domains\Identity\Actions\AuthenticateUser;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Exceptions\AccountSuspendedException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_token_for_an_active_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-horse-battery-staple'),
            'status' => UserStatus::Active,
        ]);

        $token = (new AuthenticateUser)->handle([
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ]);

        $this->assertNotEmpty($token);
    }

    public function test_it_returns_null_for_invalid_credentials(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Active]);

        $token = (new AuthenticateUser)->handle([
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertNull($token);
    }

    public function test_it_rejects_a_suspended_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-horse-battery-staple'),
            'status' => UserStatus::Suspended,
        ]);

        $this->expectException(AccountSuspendedException::class);

        (new AuthenticateUser)->handle([
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ]);
    }
}
