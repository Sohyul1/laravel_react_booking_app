<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    // Public auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Everything below requires a valid JWT (Authorization: Bearer <token>)
    Route::middleware('jwt.auth')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::apiResource('bookings', BookingController::class);
    });
});
