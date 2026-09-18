<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\CreateDestination;
use App\Domains\Destinations\Actions\DeleteDestination;
use App\Domains\Destinations\Actions\UpdateDestination;
use App\Domains\Destinations\Http\Requests\StoreDestinationRequest;
use App\Domains\Destinations\Http\Requests\UpdateDestinationRequest;
use App\Domains\Destinations\Http\Resources\DestinationResource;
use App\Domains\Destinations\Models\Destination;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $destinations = Destination::query()
            ->with(['region', 'category'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('region_id'), fn ($query, $regionId) => $query->where('region_id', $regionId))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(DestinationResource::collection($destinations));
    }

    public function store(StoreDestinationRequest $request, CreateDestination $action): JsonResponse
    {
        $destination = $action->handle($request->validated());

        return ApiResponse::success(new DestinationResource($destination), 'Destination created.', 201);
    }

    public function show(Destination $destination): JsonResponse
    {
        $destination->load(['region', 'category', 'places', 'media']);

        return ApiResponse::success(new DestinationResource($destination));
    }

    public function update(UpdateDestinationRequest $request, Destination $destination, UpdateDestination $action): JsonResponse
    {
        $destination = $action->handle($destination, $request->validated());

        return ApiResponse::success(new DestinationResource($destination));
    }

    public function destroy(Destination $destination, DeleteDestination $action): JsonResponse
    {
        $action->handle($destination);

        return ApiResponse::success(message: 'Destination deleted.');
    }
}
