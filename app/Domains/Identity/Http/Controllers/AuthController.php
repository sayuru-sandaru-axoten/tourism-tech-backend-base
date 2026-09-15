<?php

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Actions\AuthenticateUser;
use App\Domains\Identity\Actions\RegisterTraveler;
use App\Domains\Identity\Http\Requests\LoginRequest;
use App\Domains\Identity\Http\Requests\RegisterRequest;
use App\Domains\Identity\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\ApiGuard;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterTraveler $action): JsonResponse
    {
        $token = $action->handle($request->registrationAttributes());

        return $this->tokenResponse($token, 201);
    }

    public function login(LoginRequest $request, AuthenticateUser $action): JsonResponse
    {
        $token = $action->handle($request->credentials());

        if (! $token) {
            return ApiResponse::error('The supplied credentials are invalid.', 401);
        }

        return $this->tokenResponse($token);
    }

    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(ApiGuard::guard()->refresh());
    }

    public function logout(): JsonResponse
    {
        ApiGuard::guard()->logout();

        return ApiResponse::success(null, 'Successfully logged out.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    /**
     * Bootstrap data for the React admin application. Route middleware limits
     * this endpoint to staff roles; frontend navigation must still be backed by
     * authorization checks on every protected endpoint.
     */
    public function staffSession(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    private function tokenResponse(string $token, int $status = 200): JsonResponse
    {
        /** @var User $user */
        $user = ApiGuard::guard()->setToken($token)->user();

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => ApiGuard::guard()->factory()->getTTL() * 60,
            'user' => new UserResource($user),
        ], status: $status);
    }
}
