<?php

use App\Domains\Destinations\Http\Controllers\CategoryController;
use App\Domains\Destinations\Http\Controllers\CountryController;
use App\Domains\Destinations\Http\Controllers\DestinationController;
use App\Domains\Destinations\Http\Controllers\DestinationPublicController;
use App\Domains\Destinations\Http\Controllers\MediaController;
use App\Domains\Destinations\Http\Controllers\PlaceController;
use App\Domains\Destinations\Http\Controllers\RegionController;
use App\Domains\Destinations\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

// Public, unauthenticated — the traveler-facing catalogue only ever sees
// published destinations (DestinationPublicController enforces this).
Route::get('destinations', [DestinationPublicController::class, 'index']);
Route::get('destinations/{destination:slug}', [DestinationPublicController::class, 'show']);

Route::middleware('auth:api')->group(function (): void {
    // Geography + content CRUD — gated by `destinations.manage` permission.
    Route::middleware('permission:destinations.manage')->group(function (): void {
        Route::apiResource('admin/countries', CountryController::class);
        Route::apiResource('admin/regions', RegionController::class);
        Route::apiResource('admin/categories', CategoryController::class);
        Route::apiResource('admin/destinations', DestinationController::class);
        Route::apiResource('admin/destinations.places', PlaceController::class)->shallow();

        Route::get('admin/destinations/{destination}/media', [MediaController::class, 'indexForDestination']);
        Route::post('admin/destinations/{destination}/media', [MediaController::class, 'storeForDestination']);
        Route::get('admin/places/{place}/media', [MediaController::class, 'indexForPlace']);
        Route::post('admin/places/{place}/media', [MediaController::class, 'storeForPlace']);
        Route::delete('admin/media/{media}', [MediaController::class, 'destroy']);
        Route::patch('admin/media/reorder', [MediaController::class, 'reorder']);

        Route::get('admin/destinations/{destination}/translations', [TranslationController::class, 'indexForDestination']);
        Route::put('admin/destinations/{destination}/translations', [TranslationController::class, 'upsertForDestination']);
        Route::get('admin/places/{place}/translations', [TranslationController::class, 'indexForPlace']);
        Route::put('admin/places/{place}/translations', [TranslationController::class, 'upsertForPlace']);
        Route::delete('admin/translations/{translation}', [TranslationController::class, 'destroy']);
    });
});
