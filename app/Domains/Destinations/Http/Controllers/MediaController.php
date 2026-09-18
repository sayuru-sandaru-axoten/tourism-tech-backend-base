<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\DeleteMedia;
use App\Domains\Destinations\Actions\ReorderMedia;
use App\Domains\Destinations\Actions\UploadMedia;
use App\Domains\Destinations\Http\Requests\ReorderMediaRequest;
use App\Domains\Destinations\Http\Requests\StoreMediaRequest;
use App\Domains\Destinations\Http\Resources\MediaResource;
use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Media;
use App\Domains\Destinations\Models\Place;
use App\Domains\Experiences\Models\Experience;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function indexForDestination(Destination $destination): JsonResponse
    {
        return ApiResponse::success(MediaResource::collection($destination->media()->orderBy('sort_order')->get()));
    }

    public function indexForPlace(Place $place): JsonResponse
    {
        return ApiResponse::success(MediaResource::collection($place->media()->orderBy('sort_order')->get()));
    }

    public function indexForExperience(Experience $experience): JsonResponse
    {
        return ApiResponse::success(MediaResource::collection($experience->media()->orderBy('sort_order')->get()));
    }

    public function storeForDestination(StoreMediaRequest $request, Destination $destination, UploadMedia $action): JsonResponse
    {
        $media = $action->handle($destination, $request->validated());

        return ApiResponse::success(new MediaResource($media), 'Media uploaded.', 201);
    }

    public function storeForPlace(StoreMediaRequest $request, Place $place, UploadMedia $action): JsonResponse
    {
        $media = $action->handle($place, $request->validated());

        return ApiResponse::success(new MediaResource($media), 'Media uploaded.', 201);
    }

    public function storeForExperience(StoreMediaRequest $request, Experience $experience, UploadMedia $action): JsonResponse
    {
        $media = $action->handle($experience, $request->validated());

        return ApiResponse::success(new MediaResource($media), 'Media uploaded.', 201);
    }

    public function destroy(Media $media, DeleteMedia $action): JsonResponse
    {
        $action->handle($media);

        return ApiResponse::success(message: 'Media deleted.');
    }

    public function reorder(ReorderMediaRequest $request, ReorderMedia $action): JsonResponse
    {
        $action->handle($request->validated()['items']);

        return ApiResponse::success(message: 'Media reordered.');
    }
}
