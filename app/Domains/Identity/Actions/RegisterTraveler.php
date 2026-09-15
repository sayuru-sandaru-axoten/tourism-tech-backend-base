<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Events\UserRegistered;
use App\Models\User;
use App\Support\Auth\ApiGuard;
use App\Support\Contracts\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a new traveler account and returns a signed-in access token for it.
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

            return $user;
        });

        event(new UserRegistered($user));

        return ApiGuard::guard()->login($user);
    }
}
