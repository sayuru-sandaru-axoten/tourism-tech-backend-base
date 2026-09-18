<?php

use App\Domains\Destinations\Http\Controllers\MediaController;
use App\Domains\Destinations\Http\Controllers\TranslationController;
use App\Domains\Experiences\Http\Controllers\ExperienceController;
use App\Domains\Experiences\Http\Controllers\ExperienceHighlightController;
use App\Domains\Experiences\Http\Controllers\ExperiencePublicController;
use App\Domains\Experiences\Http\Controllers\PolicyController;
use Illuminate\Support\Facades\Route;

// Public, unauthenticated — the traveler-facing catalogue only ever sees
// published experiences (ExperiencePublicController enforces this).
Route::get('experiences', [ExperiencePublicController::class, 'index']);
Route::get('experiences/{experience:slug}', [ExperiencePublicController::class, 'show']);

Route::middleware('auth:api')->group(function (): void {
    // Catalog CRUD — gated by `experiences.manage` permission.
    Route::middleware('permission:experiences.manage')->group(function (): void {
        Route::apiResource('admin/experiences', ExperienceController::class);
        Route::apiResource('admin/experiences.highlights', ExperienceHighlightController::class)->shallow();
        Route::apiResource('admin/experiences.policies', PolicyController::class)->shallow();

        Route::get('admin/experiences/{experience}/media', [MediaController::class, 'indexForExperience']);
        Route::post('admin/experiences/{experience}/media', [MediaController::class, 'storeForExperience']);
        Route::get('admin/experiences/{experience}/translations', [TranslationController::class, 'indexForExperience']);
        Route::put('admin/experiences/{experience}/translations', [TranslationController::class, 'upsertForExperience']);
    });
});
