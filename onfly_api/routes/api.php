<?php

use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TravelRequestController;
use App\Http\Controllers\Api\TravelRequestLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('airports', [AirportController::class, 'index']);

    Route::apiResource('travel-requests', TravelRequestController::class)
        ->only(['index', 'store', 'show', 'update'])
        ->parameters(['travel-requests' => 'travelRequest']);

    Route::get('travel-request-logs', [TravelRequestLogController::class, 'index']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
});
