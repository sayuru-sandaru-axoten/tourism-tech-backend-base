<?php

namespace Tests\Feature\Domains\Identity;

use App\Domains\Identity\Enums\UserStatus;
use App\Models\User;
use App\Support\Auth\ApiGuard;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TravelAccessSeeder::class);
    }

    public function test_a_traveler_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ama Perera',
            'email' => 'ama@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.roles', ['traveler'])
            ->assertJsonStructure(['data' => ['access_token', 'token_type', 'expires_in', 'user']]);

        $this->assertDatabaseHas('users', ['email' => 'ama@example.com']);

        $user = User::query()->where('email', 'ama@example.com')->firstOrFail();
        $this->assertDatabaseHas('traveler_profiles', ['user_id' => $user->id]);
    }

    public function test_a_user_can_log_in_and_access_their_profile(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-horse-battery-staple')]);
        $user->assignRole('traveler');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])->assertOk()->json('data.access_token');

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_login_fails_for_a_suspended_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-horse-battery-staple'),
            'status' => UserStatus::Suspended,
        ]);
        $user->assignRole('traveler');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])->assertForbidden()
            ->assertJsonPath('message', 'This account has been suspended.');
    }

    public function test_a_token_can_be_refreshed_and_then_logged_out(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-horse-battery-staple')]);
        $user->assignRole('traveler');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])->json('data.access_token');

        $refreshed = $this->withToken($token)->postJson('/api/v1/auth/refresh-token')
            ->assertOk()
            ->json('data.access_token');

        $this->assertNotSame($token, $refreshed);

        $this->withToken($refreshed)->postJson('/api/v1/auth/logout')->assertOk();
    }

    public function test_refreshing_a_token_blacklists_the_old_one(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-horse-battery-staple')]);
        $user->assignRole('traveler');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])->json('data.access_token');

        $this->withToken($token)->postJson('/api/v1/auth/refresh-token')->assertOk();

        // Checked directly against the JWT manager rather than via a second
        // simulated HTTP call: tymon/jwt-auth caches its resolved token on a
        // container singleton once set, which a real second HTTP request
        // (fresh container per request) would not hit but a same-container
        // test call would — this asserts the actual invalidation mechanism
        // instead of an artifact of the test harness reusing one container.
        $this->expectException(TokenBlacklistedException::class);

        JWTAuth::setToken($token)->checkOrFail();
    }

    public function test_a_staff_role_can_reach_the_admin_session_bootstrap(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operations-officer');

        $token = ApiGuard::guard()->login($user);

        $this->withToken($token)->getJson('/api/v1/admin/session')
            ->assertOk()
            ->assertJsonPath('data.roles', ['operations-officer']);
    }

    public function test_a_plain_traveler_cannot_reach_the_admin_session_bootstrap(): void
    {
        $user = User::factory()->create();
        $user->assignRole('traveler');

        $token = ApiGuard::guard()->login($user);

        $this->withToken($token)->getJson('/api/v1/admin/session')
            ->assertForbidden();
    }

    public function test_an_administrator_can_change_a_users_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $adminToken = ApiGuard::guard()->login($admin);

        $target = User::factory()->create(['status' => UserStatus::Active]);

        $this->withToken($adminToken)
            ->patchJson("/api/v1/admin/users/{$target->id}/status", ['status' => 'suspended'])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertSame(UserStatus::Suspended, $target->fresh()->status);
    }

    public function test_a_non_privileged_user_cannot_change_a_users_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('traveler');
        $token = ApiGuard::guard()->login($user);

        $target = User::factory()->create(['status' => UserStatus::Active]);

        $this->withToken($token)
            ->patchJson("/api/v1/admin/users/{$target->id}/status", ['status' => 'suspended'])
            ->assertForbidden();
    }

    public function test_changing_status_rejects_an_invalid_value(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $adminToken = ApiGuard::guard()->login($admin);

        $target = User::factory()->create(['status' => UserStatus::Active]);

        $this->withToken($adminToken)
            ->patchJson("/api/v1/admin/users/{$target->id}/status", ['status' => 'not-a-real-status'])
            ->assertUnprocessable();
    }
}
