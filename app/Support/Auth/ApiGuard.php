<?php

namespace App\Support\Auth;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTGuard;

/**
 * `auth('api')` is typed as the generic `Guard`/`StatefulGuard` interface by
 * Laravel and PHPStan, which don't declare `login()`/`refresh()`/`setToken()`/
 * `factory()`. This narrows to the concrete guard that `config/auth.php`
 * pins the `api` guard to ('driver' => 'jwt'), in one place instead of an
 * unchecked assumption repeated at every `auth('api')` call site.
 */
final class ApiGuard
{
    public static function guard(): JWTGuard
    {
        $guard = Auth::guard('api');

        assert($guard instanceof JWTGuard);

        return $guard;
    }
}
