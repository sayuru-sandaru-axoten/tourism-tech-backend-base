<?php

namespace App\Domains\Experiences\Http\Controllers;

use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Http\Resources\ExperienceResource;
use App\Domains\Experiences\Models\Experience;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperiencePublicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $experiences = Experience::query()
            ->published()
            ->with(['destination', 'partner', 'categories'])
            ->when($request->integer('destination_id'), fn ($query, $destinationId) => $query->where('destination_id', $destinationId))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId)))
            ->orderBy('title')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(ExperienceResource::collection($experiences));
    }

    public function show(Experience $experience): JsonResponse
    {
        abort_unless($experience->status === ExperienceStatus::Published, 404);

        $experience->load(['destination', 'partner', 'categories', 'highlights', 'policies', 'media']);

        return ApiResponse::success(new ExperienceResource($experience));
    }
}
