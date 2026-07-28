<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentRequest;
use App\Models\Rent;
use App\Models\Scooter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RentController extends Controller
{
    /**
     * Мои аренды (текущего пользователя)
     * GET /api/rents
     */
    public function myRents(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Rent::with('scooter')
            ->byPhone($user->phone);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('start_time', 'desc');

        $rents = $query->paginate($request->get('per_page', 15));

        $formattedData = $rents->map(function ($rent) {
            return [
                'id' => $rent->id,
                'scooter' => [
                    'id' => $rent->scooter->id,
                    'model' => $rent->scooter->model,
                ],
                'start_time' => $rent->start_time,
                'end_time' => $rent->end_time,
                'duration_minutes' => $rent->duration_in_minutes,
                'cost_rub' => $rent->cost,
                'status' => $rent->status,
            ];
        });

        return response()->json([
            'message' => 'Мои аренды',
            'data' => $formattedData,
            'pagination' => [
                'current_page' => $rents->currentPage(),
                'last_page' => $rents->lastPage(),
                'per_page' => $rents->perPage(),
                'total' => $rents->total(),
            ],
        ]);
    }

    /**
     * Создать аренду
     * POST /api/rents
     */
    public function store(RentRequest $request): JsonResponse
    {
        $user = Auth::user();

        $scooter = Scooter::findOrFail($request->scooter_id);

        // Проверка доступности
        if ($scooter->status !== Scooter::STATUS_AVAILABLE) {
            return response()->json([
                'message' => 'Самокат недоступен',
                'error' => "Статус: {$scooter->status}",
            ], 422);
        }

        // Проверка заряда
        if ($scooter->battery_level < 20) {
            return response()->json([
                'message' => 'Недостаточный заряд',
                'error' => "Заряд: {$scooter->battery_level}%",
            ], 422);
        }

        // Проверка на существующую аренду
        $activeRent = Rent::active()->byPhone($user->phone)->first();
        if ($activeRent) {
            return response()->json([
                'message' => 'У вас уже есть активная аренда',
                'active_rent_id' => $activeRent->id,
            ], 422);
        }

        // Создаём аренду
        $rent = Rent::create([
            'user_phone' => $user->phone,
            'scooter_id' => $request->scooter_id,
            'start_time' => Carbon::now(),
            'status' => Rent::STATUS_ACTIVE,
        ]);

        // Меняем статус самоката
        $scooter->update(['status' => Scooter::STATUS_IN_USE]);

        $rent->load('scooter');

        return response()->json([
            'message' => 'Аренда начата',
            'data' => [
                'id' => $rent->id,
                'scooter' => [
                    'id' => $scooter->id,
                    'model' => $scooter->model,
                ],
                'start_time' => $rent->start_time,
                'status' => $rent->status,
            ],
        ], 201);
    }

    /**
     * Завершить аренду
     * PUT /api/rents/{id}/complete
     */
    public function complete(int $id): JsonResponse
    {
        $user = Auth::user();
        $rent = Rent::findOrFail($id);

        // Проверка: только владелец
        if ($rent->user_phone !== $user->phone) {
            return response()->json([
                'message' => 'Это не ваша аренда',
            ], 403);
        }

        if (!$rent->isActive()) {
            return response()->json([
                'message' => 'Аренда уже завершена',
            ], 422);
        }

        $rent->complete();
        $rent->refresh();

        return response()->json([
            'message' => 'Аренда завершена',
            'data' => [
                'id' => $rent->id,
                'scooter_model' => $rent->scooter->model,
                'start_time' => $rent->start_time,
                'end_time' => $rent->end_time,
                'duration_minutes' => $rent->duration_in_minutes,
                'cost_rub' => $rent->cost,
                'status' => $rent->status,
            ],
        ]);
    }

    /**
     * Активная аренда
     * GET /api/rents/active
     */
    public function active(): JsonResponse
    {
        $user = Auth::user();

        $activeRent = Rent::active()
            ->byPhone($user->phone)
            ->with('scooter')
            ->first();

        if (!$activeRent) {
            return response()->json([
                'message' => 'Нет активных аренд',
                'has_active_rent' => false,
            ]);
        }

        $duration = Carbon::now()->diffInMinutes($activeRent->start_time);

        return response()->json([
            'message' => 'Активная аренда',
            'has_active_rent' => true,
            'data' => [
                'id' => $activeRent->id,
                'scooter' => [
                    'id' => $activeRent->scooter->id,
                    'model' => $activeRent->scooter->model,
                    'battery_level' => $activeRent->scooter->battery_level,
                ],
                'start_time' => $activeRent->start_time,
                'current_duration_minutes' => $duration,
                'estimated_cost_rub' => round($duration * 5, 2),
                'status' => $activeRent->status,
            ],
        ]);
    }

    /**
     * История аренд
     * GET /api/rents/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();

        $rents = Rent::completed()
            ->byPhone($user->phone)
            ->with('scooter')
            ->orderBy('end_time', 'desc')
            ->paginate($request->get('per_page', 10));

        $formattedData = $rents->map(function ($rent) {
            return [
                'id' => $rent->id,
                'scooter_model' => $rent->scooter->model,
                'start_time' => $rent->start_time,
                'end_time' => $rent->end_time,
                'duration_minutes' => $rent->duration_in_minutes,
                'cost_rub' => $rent->cost,
            ];
        });

        return response()->json([
            'message' => 'История аренд',
            'data' => $formattedData,
            'total_rides' => $rents->total(),
            'total_spent' => round($rents->sum('cost'), 2),
            'pagination' => [
                'current_page' => $rents->currentPage(),
                'last_page' => $rents->lastPage(),
                'per_page' => $rents->perPage(),
                'total' => $rents->total(),
            ],
        ]);
    }
}
