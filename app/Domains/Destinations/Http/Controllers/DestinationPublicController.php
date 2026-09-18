<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Enums\DestinationStatus;
use App\Domains\Destinations\Http\Resources\DestinationResource;
use App\Domains\Destinations\Models\Destination;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationPublicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $destinations = Destination::query()
            ->published()
            ->with(['region', 'category'])
            ->when($request->integer('region_id'), fn ($query, $regionId) => $query->where('region_id', $regionId))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(DestinationResource::collection($destinations));
    }

    public function show(Destination $destination): JsonResponse
    {
        abort_unless($destination->status === DestinationStatus::Published, 404);

        $destination->load(['region', 'category', 'places', 'media']);

        return ApiResponse::success(new DestinationResource($destination));
    }
}
