<?php

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Actions\UpdateTravelerProfile;
use App\Domains\Identity\Http\Requests\UpdateTravelerProfileRequest;
use App\Domains\Identity\Http\Resources\TravelerProfileResource;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelerProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()?->travelerProfile;

        abort_if($profile === null, 404, 'Traveler profile not found.');

        return ApiResponse::success(new TravelerProfileResource($profile));
    }

    public function update(UpdateTravelerProfileRequest $request, UpdateTravelerProfile $action): JsonResponse
    {
        $profile = $request->user()?->travelerProfile;

        abort_if($profile === null, 404, 'Traveler profile not found.');

        $profile = $action->handle($profile, $request->validated());

        return ApiResponse::success(new TravelerProfileResource($profile));
    }
}
