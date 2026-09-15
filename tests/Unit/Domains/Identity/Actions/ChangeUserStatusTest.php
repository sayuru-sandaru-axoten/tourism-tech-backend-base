<?php

namespace Tests\Unit\Domains\Identity\Actions;

use App\Domains\Identity\Actions\ChangeUserStatus;
use App\Domains\Identity\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeUserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_the_users_status(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Active]);

        $updated = (new ChangeUserStatus)->handle($user, UserStatus::Suspended);

        $this->assertSame(UserStatus::Suspended, $updated->status);
        $this->assertSame(UserStatus::Suspended, $user->fresh()->status);
    }
}
