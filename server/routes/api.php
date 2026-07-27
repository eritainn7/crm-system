<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScooterController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Аутентификация
|--------------------------------------------------------------------------
*/

Route::post('/auth/reg', [AuthController::class, 'register']);
Route::post('/auth/log', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/out', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| API Routes - Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| API Routes - Самокаты
|--------------------------------------------------------------------------
*/

Route::get('/scooters/available', [ScooterController::class, 'available']);

Route::middleware('auth:sanctum')->prefix('management/scooters')->group(function () {
    Route::get('/', [ScooterController::class, 'index']);
    Route::post('/', [ScooterController::class, 'store']);
    Route::get('/{id}', [ScooterController::class, 'show']);
    Route::put('/{id}', [ScooterController::class, 'update']);
    Route::delete('/{id}', [ScooterController::class, 'destroy']);
    Route::post('/batch-status', [ScooterController::class, 'batchUpdateStatus']);
});

/*
|--------------------------------------------------------------------------
| API Routes - Аренда
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('rents')->group(function () {
    Route::get('/active', [RentController::class, 'active']);
    Route::get('/history', [RentController::class, 'history']);
});

Route::middleware('auth:sanctum')->prefix('management/rents')->group(function () {
    Route::get('/', [RentController::class, 'index']);
    Route::post('/', [RentController::class, 'store']);
    Route::get('/stats', [RentController::class, 'stats']);
    Route::get('/{id}', [RentController::class, 'show']);
    Route::put('/{id}/complete', [RentController::class, 'complete']);
});
