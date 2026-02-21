<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\GlucoseReadingController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
| All routes here are prefixed with /api/v1
*/

// Auth routes
Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::post('/google', [AuthController::class, 'googleLogin']);

});

// Profile routes
Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/show', [UserProfileController::class, 'show']);
    Route::put('/update', [UserProfileController::class, 'update']);
});

// Glucose logging (blood glucose time-series, user-scoped)
Route::middleware('auth:sanctum')->prefix('glucose')->group(function () {
    Route::get('/', [GlucoseReadingController::class, 'index']);
    Route::post('/', [GlucoseReadingController::class, 'store']);
    Route::get('/{id}', [GlucoseReadingController::class, 'show']);
    Route::put('/{id}', [GlucoseReadingController::class, 'update']);
    Route::delete('/{id}', [GlucoseReadingController::class, 'destroy']);
});

