<?php

namespace App\Http\Controllers;

use App\Models\Rent;
use App\Models\Scooter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Получить сводную информацию для авторизованного пользователя
     * GET /api/dashboard
     */
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Количество самокатов по статусам
        $scootersByStatus = Scooter::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Заполняем нулями отсутствующие статусы
        $allStatuses = Scooter::getStatuses();
        $scooterStats = [];
        foreach ($allStatuses as $status) {
            $scooterStats[$status] = $scootersByStatus[$status] ?? 0;
        }

        $totalScooters = array_sum($scooterStats);

        // 2. Количество активных аренд текущего пользователя
        $activeRentsCount = Rent::active()
            ->byPhone($user->phone)
            ->count();

        // Активная аренда (если есть)
        $activeRent = Rent::active()
            ->byPhone($user->phone)
            ->with('scooter')
            ->first();

        $activeRentData = null;
        if ($activeRent) {
            $activeRentData = [
                'id' => $activeRent->id,
                'scooter_model' => $activeRent->scooter->model,
                'start_time' => $activeRent->start_time,
                'duration_minutes' => now()->diffInMinutes($activeRent->start_time),
            ];
        }

        // 3. Средний уровень заряда самокатов
        $averageBattery = Scooter::avg('battery_level');
        $averageBattery = $averageBattery ? round($averageBattery, 1) : 0;

        // Средний заряд по статусам
        $batteryByStatus = Scooter::selectRaw('status, AVG(battery_level) as avg_battery')
            ->groupBy('status')
            ->pluck('avg_battery', 'status')
            ->map(function ($value) {
                return round($value, 1);
            })
            ->toArray();

        // Заполняем нулями отсутствующие статусы
        $batteryStats = [];
        foreach ($allStatuses as $status) {
            $batteryStats[$status] = $batteryByStatus[$status] ?? 0;
        }

        // Дополнительная статистика
        $totalRents = Rent::count();
        $totalCompletedRents = Rent::completed()->count();
        $totalActiveRents = Rent::active()->count();

        return response()->json([
            'message' => 'Информация панели управления',
            'data' => [
                // Статистика самокатов
                'scooters' => [
                    'total' => $totalScooters,
                    'by_status' => [
                        'available' => $scooterStats['available'],
                        'in_use' => $scooterStats['in_use'],
                        'maintenance' => $scooterStats['maintenance'],
                        'offline' => $scooterStats['offline'],
                    ],
                    'battery' => [
                        'average' => $averageBattery,
                        'by_status' => [
                            'available' => $batteryStats['available'],
                            'in_use' => $batteryStats['in_use'],
                            'maintenance' => $batteryStats['maintenance'],
                            'offline' => $batteryStats['offline'],
                        ],
                    ],
                ],
                
                // Статистика пользователя
                'user' => [
                    'phone' => $user->phone,
                    'full_name' => $user->full_name,
                    'active_rents_count' => $activeRentsCount,
                    'has_active_rent' => $activeRentsCount > 0,
                    'active_rent' => $activeRentData,
                ],
                
                // Общая статистика аренд
                'rents' => [
                    'total' => $totalRents,
                    'active' => $totalActiveRents,
                    'completed' => $totalCompletedRents,
                ],
            ],
        ]);
    }
}
