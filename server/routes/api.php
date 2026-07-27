<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScooterController;
use App\Http\Controllers\RentController;
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
| API Routes - Самокаты
|--------------------------------------------------------------------------
*/

// Публичный доступ (без авторизации)
Route::get('/scooters/available', [ScooterController::class, 'available']);

// Управление (требуется авторизация)
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

// Эндпоинты для пользователей (требуется авторизация)
Route::middleware('auth:sanctum')->prefix('rents')->group(function () {
    Route::get('/active', [RentController::class, 'active']);   // Только своя активная аренда
    Route::get('/history', [RentController::class, 'history']); // Только своя история
});

// Управление арендами (для менеджеров/админов)
Route::middleware('auth:sanctum')->prefix('management/rents')->group(function () {
    Route::get('/', [RentController::class, 'index']);               // Все аренды
    Route::post('/', [RentController::class, 'store']);               // Создать аренду
    Route::get('/stats', [RentController::class, 'stats']);           // Статистика
    Route::get('/{id}', [RentController::class, 'show']);             // Просмотр аренды
    Route::put('/{id}/complete', [RentController::class, 'complete']); // Завершить аренду
});
