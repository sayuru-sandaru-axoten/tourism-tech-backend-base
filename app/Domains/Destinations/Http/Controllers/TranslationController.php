<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\DeleteTranslation;
use App\Domains\Destinations\Actions\UpsertTranslation;
use App\Domains\Destinations\Http\Requests\UpsertTranslationRequest;
use App\Domains\Destinations\Http\Resources\TranslationResource;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Domains\Destinations\Models\Translation;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    public function indexForDestination(Destination $destination): JsonResponse
    {
        return ApiResponse::success(TranslationResource::collection($destination->translations()->get()));
    }

    public function indexForPlace(Place $place): JsonResponse
    {
        return ApiResponse::success(TranslationResource::collection($place->translations()->get()));
    }

    public function upsertForDestination(UpsertTranslationRequest $request, Destination $destination, UpsertTranslation $action): JsonResponse
    {
        $translation = $action->handle($destination, $request->validated());

        return ApiResponse::success(new TranslationResource($translation), 'Translation saved.');
    }

    public function upsertForPlace(UpsertTranslationRequest $request, Place $place, UpsertTranslation $action): JsonResponse
    {
        $translation = $action->handle($place, $request->validated());

        return ApiResponse::success(new TranslationResource($translation), 'Translation saved.');
    }

    public function destroy(Translation $translation, DeleteTranslation $action): JsonResponse
    {
        $action->handle($translation);

        return ApiResponse::success(message: 'Translation deleted.');
    }
}
