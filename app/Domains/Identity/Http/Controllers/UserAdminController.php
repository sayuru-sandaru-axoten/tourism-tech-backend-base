<?php

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Actions\ChangeUserStatus;
use App\Domains\Identity\Http\Requests\ChangeUserStatusRequest;
use App\Domains\Identity\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserAdminController extends Controller
{
    public function updateStatus(ChangeUserStatusRequest $request, User $user, ChangeUserStatus $action): JsonResponse
    {
        $user = $action->handle($user, $request->status());

        return ApiResponse::success(new UserResource($user));
    }
}
