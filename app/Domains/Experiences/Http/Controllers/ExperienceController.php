<?php

namespace App\Domains\Experiences\Http\Controllers;

use App\Domains\Experiences\Actions\CreateExperience;
use App\Domains\Experiences\Actions\DeleteExperience;
use App\Domains\Experiences\Actions\UpdateExperience;
use App\Domains\Experiences\Http\Requests\StoreExperienceRequest;
use App\Domains\Experiences\Http\Requests\UpdateExperienceRequest;
use App\Domains\Experiences\Http\Resources\ExperienceResource;
use App\Domains\Experiences\Models\Experience;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $experiences = Experience::query()
            ->with(['destination', 'partner', 'categories'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('destination_id'), fn ($query, $destinationId) => $query->where('destination_id', $destinationId))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId)))
            ->orderBy('title')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(ExperienceResource::collection($experiences));
    }

    public function store(StoreExperienceRequest $request, CreateExperience $action): JsonResponse
    {
        $experience = $action->handle($request->validated());

        return ApiResponse::success(new ExperienceResource($experience), 'Experience created.', 201);
    }

    public function show(Experience $experience): JsonResponse
    {
        $experience->load(['destination', 'partner', 'categories', 'highlights', 'policies', 'media']);

        return ApiResponse::success(new ExperienceResource($experience));
    }

    public function update(UpdateExperienceRequest $request, Experience $experience, UpdateExperience $action): JsonResponse
    {
        $experience = $action->handle($experience, $request->validated());

        return ApiResponse::success(new ExperienceResource($experience));
    }

    public function destroy(Experience $experience, DeleteExperience $action): JsonResponse
    {
        $action->handle($experience);

        return ApiResponse::success(message: 'Experience deleted.');
    }
}
