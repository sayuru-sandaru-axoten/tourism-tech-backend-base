<?php

namespace App\Domains\Experiences\Http\Controllers;

use App\Domains\Experiences\Actions\CreateExperienceHighlight;
use App\Domains\Experiences\Actions\DeleteExperienceHighlight;
use App\Domains\Experiences\Actions\UpdateExperienceHighlight;
use App\Domains\Experiences\Http\Requests\StoreExperienceHighlightRequest;
use App\Domains\Experiences\Http\Requests\UpdateExperienceHighlightRequest;
use App\Domains\Experiences\Http\Resources\ExperienceHighlightResource;
use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\ExperienceHighlight;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ExperienceHighlightController extends Controller
{
    public function index(Experience $experience): JsonResponse
    {
        $highlights = $experience->highlights()->orderBy('sort_order')->get();

        return ApiResponse::success(ExperienceHighlightResource::collection($highlights));
    }

    public function store(StoreExperienceHighlightRequest $request, Experience $experience, CreateExperienceHighlight $action): JsonResponse
    {
        $highlight = $action->handle($experience, $request->validated());

        return ApiResponse::success(new ExperienceHighlightResource($highlight), 'Highlight created.', 201);
    }

    public function show(ExperienceHighlight $highlight): JsonResponse
    {
        return ApiResponse::success(new ExperienceHighlightResource($highlight));
    }

    public function update(UpdateExperienceHighlightRequest $request, ExperienceHighlight $highlight, UpdateExperienceHighlight $action): JsonResponse
    {
        $highlight = $action->handle($highlight, $request->validated());

        return ApiResponse::success(new ExperienceHighlightResource($highlight));
    }

    public function destroy(ExperienceHighlight $highlight, DeleteExperienceHighlight $action): JsonResponse
    {
        $action->handle($highlight);

        return ApiResponse::success(message: 'Highlight deleted.');
    }
}
