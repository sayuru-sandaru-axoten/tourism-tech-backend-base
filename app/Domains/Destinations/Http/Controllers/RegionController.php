<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\CreateRegion;
use App\Domains\Destinations\Actions\DeleteRegion;
use App\Domains\Destinations\Actions\UpdateRegion;
use App\Domains\Destinations\Http\Requests\StoreRegionRequest;
use App\Domains\Destinations\Http\Requests\UpdateRegionRequest;
use App\Domains\Destinations\Http\Resources\RegionResource;
use App\Domains\Destinations\Models\Region;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $regions = Region::query()
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->where('country_id', $countryId))
            ->orderBy('name')
            ->get();

        return ApiResponse::success(RegionResource::collection($regions));
    }

    public function store(StoreRegionRequest $request, CreateRegion $action): JsonResponse
    {
        $region = $action->handle($request->validated());

        return ApiResponse::success(new RegionResource($region), 'Region created.', 201);
    }

    public function show(Region $region): JsonResponse
    {
        return ApiResponse::success(new RegionResource($region));
    }

    public function update(UpdateRegionRequest $request, Region $region, UpdateRegion $action): JsonResponse
    {
        $region = $action->handle($region, $request->validated());

        return ApiResponse::success(new RegionResource($region));
    }

    public function destroy(Region $region, DeleteRegion $action): JsonResponse
    {
        $action->handle($region);

        return ApiResponse::success(message: 'Region deleted.');
    }
}
