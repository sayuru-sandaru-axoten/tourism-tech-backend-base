<?php

namespace Tests\Unit\Domains\Identity\Actions;

use App\Domains\Identity\Actions\RegisterTraveler;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Events\UserRegistered;
use App\Models\User;
use Database\Seeders\TravelAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegisterTravelerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_traveler_and_returns_a_token(): void
    {
        $this->seed(TravelAccessSeeder::class);
        Event::fake([UserRegistered::class]);

        $token = (new RegisterTraveler)->handle([
            'name' => 'Ama Perera',
            'email' => 'ama@example.com',
            'password' => 'correct-horse-battery-staple',
        ]);

        $this->assertNotEmpty($token);

        $user = User::query()->where('email', 'ama@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('traveler'));
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertTrue(auth('api')->setToken($token)->user()->is($user));

        Event::assertDispatched(
            UserRegistered::class,
            fn (UserRegistered $event): bool => $event->user->is($user),
        );
    }
}
