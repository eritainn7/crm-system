<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScooterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Аутентификация
|--------------------------------------------------------------------------
*/

// Публичные маршруты аутентификации
Route::post('/auth/reg', [AuthController::class, 'register']);
Route::post('/auth/log', [AuthController::class, 'login']);

// Защищённые маршруты аутентификации
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/out', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| API Routes - Управление самокатами
|--------------------------------------------------------------------------
*/

// Публичный маршрут для получения доступных самокатов
Route::get('/scooters/available', [ScooterController::class, 'available']);

// Защищённые маршруты управления самокатами (CRUD)
Route::middleware('auth:sanctum')->prefix('management/scooters')->group(function () {
    Route::get('/', [ScooterController::class, 'index']);                    // Список всех
    Route::post('/', [ScooterController::class, 'store']);                   // Создать
    Route::get('/{id}', [ScooterController::class, 'show']);                 // Просмотр
    Route::put('/{id}', [ScooterController::class, 'update']);               // Обновить
    Route::delete('/{id}', [ScooterController::class, 'destroy']);           // Удалить
    Route::post('/batch-status', [ScooterController::class, 'batchUpdateStatus']); // Массовое обновление
});
