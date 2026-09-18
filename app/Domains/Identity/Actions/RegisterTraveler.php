<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Events\UserRegistered;
use App\Models\User;
use App\Support\Auth\ApiGuard;
use App\Support\Contracts\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * The public self-registration entry point (`POST /api/v1/auth/register`) —
 * always produces a `traveler`-role account with a matching TravelerProfile.
 * Not a generic "create a user" helper: a staff or partner account needs its
 * own Action creating its own profile type, never this one.
 */
class RegisterTraveler implements Action
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function handle(array $attributes): string
    {
        $user = DB::transaction(function () use ($attributes): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
            ]);

            $user->assignRole('traveler');
            $user->travelerProfile()->create([]);

            return $user;
        });

        event(new UserRegistered($user));

        return ApiGuard::guard()->login($user);
    }
}
