<?php

namespace App\Domains\Partners\Http\Controllers;

use App\Domains\Partners\Actions\CreatePartnerUser;
use App\Domains\Partners\Http\Requests\StorePartnerUserRequest;
use App\Domains\Partners\Http\Resources\PartnerProfileResource;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PartnerUserController extends Controller
{
    public function store(StorePartnerUserRequest $request, CreatePartnerUser $action): JsonResponse
    {
        $profile = $action->handle($request->validated());

        return ApiResponse::success(new PartnerProfileResource($profile), 'Partner account created.', 201);
    }
}
