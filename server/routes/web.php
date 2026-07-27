<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Временные API маршруты
Route::prefix('api')->group(function () {
    Route::post('/auth/reg', [App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/auth/log', [App\Http\Controllers\AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/out', [App\Http\Controllers\AuthController::class, 'logout']);
    });
});
