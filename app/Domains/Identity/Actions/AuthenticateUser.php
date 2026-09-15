<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Exceptions\AccountSuspendedException;
use App\Models\User;
use App\Support\Auth\ApiGuard;
use App\Support\Contracts\Action;

/**
 * Verifies credentials and returns a signed access token. Returns null for
 * invalid credentials (401); throws for a suspended account (403) so the
 * controller can tell the two apart.
 */
class AuthenticateUser implements Action
{
    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws AccountSuspendedException
     */
    public function handle(array $credentials): ?string
    {
        $token = ApiGuard::guard()->attempt($credentials);

        if (! is_string($token)) {
            return null;
        }

        /** @var User $user */
        $user = ApiGuard::guard()->user();

        if ($user->status === UserStatus::Suspended) {
            ApiGuard::guard()->logout();

            throw new AccountSuspendedException;
        }

        return $token;
    }
}
