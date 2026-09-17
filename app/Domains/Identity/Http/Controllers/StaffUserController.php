<?php

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Actions\CreateStaffUser;
use App\Domains\Identity\Http\Requests\StoreStaffUserRequest;
use App\Domains\Identity\Http\Resources\StaffProfileResource;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class StaffUserController extends Controller
{
    public function store(StoreStaffUserRequest $request, CreateStaffUser $action): JsonResponse
    {
        $profile = $action->handle($request->validated());

        return ApiResponse::success(new StaffProfileResource($profile), 'Staff account created.', 201);
    }
}
