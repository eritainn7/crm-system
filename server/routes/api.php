<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScooterController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Аутентификация
|--------------------------------------------------------------------------
*/

Route::post('/auth/reg', [AuthController::class, 'register']);
Route::post('/auth/log', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/out', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Дашборд
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Самокаты
|--------------------------------------------------------------------------
*/

// Публичный доступ
Route::get('/scooters/available', [ScooterController::class, 'available']);

// Управление самокатами
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
| Аренды (только свои)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('rents')->group(function () {
    Route::get('/', [RentController::class, 'myRents']);        // Мои аренды
    Route::get('/active', [RentController::class, 'active']);   // Активная аренда
    Route::get('/history', [RentController::class, 'history']); // История аренд
    Route::post('/', [RentController::class, 'store']);          // Создать аренду
    Route::put('/{id}/complete', [RentController::class, 'complete']); // Завершить
});
