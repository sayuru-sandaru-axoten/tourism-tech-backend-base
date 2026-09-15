<?php

use App\Domains\Identity\Http\Controllers\AuthController;
use App\Domains\Identity\Http\Controllers\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function (): void {
    Route::post('auth/refresh-token', [AuthController::class, 'refresh'])->middleware('throttle:auth');
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Permission-based, not a hardcoded role list (ARCHITECTURE.md §5) — every staff
    // role is granted `admin.access` in TravelAccessSeeder; adding a new staff role
    // later only means editing the seeder, not this route.
    Route::get('admin/session', [AuthController::class, 'staffSession'])
        ->middleware('permission:admin.access');

    Route::patch('admin/users/{user}/status', [UserAdminController::class, 'updateStatus'])
        ->middleware('permission:users.manage');
});
