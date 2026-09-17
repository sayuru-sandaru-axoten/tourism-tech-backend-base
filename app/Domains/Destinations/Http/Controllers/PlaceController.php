<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\CreatePlace;
use App\Domains\Destinations\Actions\DeletePlace;
use App\Domains\Destinations\Actions\UpdatePlace;
use App\Domains\Destinations\Http\Requests\StorePlaceRequest;
use App\Domains\Destinations\Http\Requests\UpdatePlaceRequest;
use App\Domains\Destinations\Http\Resources\PlaceResource;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PlaceController extends Controller
{
    public function index(Destination $destination): JsonResponse
    {
        $places = $destination->places()->orderBy('name')->get();

        return ApiResponse::success(PlaceResource::collection($places));
    }

    public function store(StorePlaceRequest $request, Destination $destination, CreatePlace $action): JsonResponse
    {
        $place = $action->handle($destination, $request->validated());

        return ApiResponse::success(new PlaceResource($place), 'Place created.', 201);
    }

    public function show(Place $place): JsonResponse
    {
        $place->load('media');

        return ApiResponse::success(new PlaceResource($place));
    }

    public function update(UpdatePlaceRequest $request, Place $place, UpdatePlace $action): JsonResponse
    {
        $place = $action->handle($place, $request->validated());

        return ApiResponse::success(new PlaceResource($place));
    }

    public function destroy(Place $place, DeletePlace $action): JsonResponse
    {
        $action->handle($place);

        return ApiResponse::success(message: 'Place deleted.');
    }
}
