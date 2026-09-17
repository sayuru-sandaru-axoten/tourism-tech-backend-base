<?php

use App\Domains\Partners\Http\Controllers\PartnerUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function (): void {
    Route::middleware('permission:partners.manage')->group(function (): void {
        Route::post('admin/partners/users', [PartnerUserController::class, 'store']);
    });
});
