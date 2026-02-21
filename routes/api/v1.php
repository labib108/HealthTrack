<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\GlucoseReadingController;
use App\Http\Controllers\Api\V1\UserConditionController;
use App\Http\Controllers\Api\V1\DrugController;
use App\Http\Controllers\Api\V1\PrescriptionController;
use App\Http\Controllers\Api\V1\AdministrationLogController;
use App\Http\Controllers\Api\V1\MedicationSuggestionController;

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


Route::middleware('auth:sanctum')->group(function () {

    // Glucose logging (blood glucose time-series, user-scoped)
    Route::get('/glucose', [GlucoseReadingController::class, 'index']);
    Route::post('/glucose', [GlucoseReadingController::class, 'store']);
    Route::get('/glucose/{id}', [GlucoseReadingController::class, 'show']);
    Route::put('/glucose/{id}', [GlucoseReadingController::class, 'update']);
    Route::delete('/glucose/{id}', [GlucoseReadingController::class, 'destroy']);

    // Conditions (chronic conditions per user)
    Route::get('/conditions', [UserConditionController::class, 'index']);
    Route::post('/conditions', [UserConditionController::class, 'store']);
    Route::get('/conditions/{id}', [UserConditionController::class, 'show']);
    Route::put('/conditions/{id}', [UserConditionController::class, 'update']);
    Route::delete('/conditions/{id}', [UserConditionController::class, 'destroy']);

    // Drugs (master data - list for dropdowns)
    Route::get('/drugs', [DrugController::class, 'index']);
    Route::get('/drugs/{id}', [DrugController::class, 'show']);

    // Prescriptions (user prescriptions per condition)
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show']);
    Route::put('/prescriptions/{id}', [PrescriptionController::class, 'update']);
    Route::delete('/prescriptions/{id}', [PrescriptionController::class, 'destroy']);

    // Medication suggestions (from rule engine)
    Route::get('/medication-suggestions', [MedicationSuggestionController::class, 'index']);
    Route::get('/medication-suggestions/{id}', [MedicationSuggestionController::class, 'show']);
    Route::post('/medication-suggestions/{id}/acknowledge', [MedicationSuggestionController::class, 'acknowledge']);
});





// Administration logs (medication actually taken - append-only)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/administration-logs', [AdministrationLogController::class, 'index']);
    Route::post('/administration-logs', [AdministrationLogController::class, 'store']);
    Route::get('/administration-logs/{id}', [AdministrationLogController::class, 'show']);
});


Route::middleware('auth:sanctum')->group(function () {
    
});

