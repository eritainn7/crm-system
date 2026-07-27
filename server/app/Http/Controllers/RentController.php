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
     * Создать новую аренду для ТЕКУЩЕГО пользователя
     * POST /api/management/rents
     */
    public function store(RentRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $phone = $user->phone;

        $scooter = Scooter::findOrFail($request->scooter_id);

        // Проверяем, доступен ли самокат
        if ($scooter->status !== Scooter::STATUS_AVAILABLE) {
            return response()->json([
                'message' => 'Самокат недоступен для аренды',
                'error' => "Текущий статус: {$scooter->status}",
            ], 422);
        }

        // Проверяем заряд батареи
        if ($scooter->battery_level < 20) {
            return response()->json([
                'message' => 'Недостаточный заряд для аренды',
                'error' => "Текущий заряд: {$scooter->battery_level}%",
            ], 422);
        }

        // Проверяем, нет ли активной аренды у пользователя
        $activeRent = Rent::active()->byPhone($phone)->first();
        if ($activeRent) {
            return response()->json([
                'message' => 'У вас уже есть активная аренда',
                'active_rent_id' => $activeRent->id,
                'scooter_model' => $activeRent->scooter->model,
                'start_time' => $activeRent->start_time,
            ], 422);
        }

        // Создаём аренду (телефон берём из авторизованного пользователя)
        $rent = Rent::create([
            'user_phone' => $phone,  // ✅ Из токена, а не из запроса!
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
                'user_phone' => $rent->user_phone,
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
     * PUT /api/management/rents/{id}/complete
     */
    public function complete(int $id): JsonResponse
    {
        $rent = Rent::findOrFail($id);

        if (!$rent->isActive()) {
            return response()->json([
                'message' => 'Аренда уже завершена',
                'data' => [
                    'id' => $rent->id,
                    'status' => $rent->status,
                    'end_time' => $rent->end_time,
                ],
            ], 422);
        }

        $rent->complete();
        $rent->refresh();

        return response()->json([
            'message' => 'Аренда завершена',
            'data' => [
                'id' => $rent->id,
                'user_phone' => $rent->user_phone,
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
     * Получить список аренд с фильтрацией (для менеджеров)
     * GET /api/management/rents
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rent::with('scooter');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_phone')) {
            $query->byPhone($request->user_phone);
        }

        if ($request->has('scooter_id')) {
            $query->where('scooter_id', $request->scooter_id);
        }

        if ($request->has('date_from')) {
            $query->where('start_time', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('start_time', '<=', $request->date_to);
        }

        $sortField = $request->get('sort_by', 'start_time');
        $sortDirection = $request->get('sort_direction', 'desc');
        $allowedSortFields = ['id', 'start_time', 'end_time', 'status', 'created_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->get('per_page', 15);
        $rents = $query->paginate($perPage);

        $formattedData = $rents->map(function ($rent) {
            return [
                'id' => $rent->id,
                'user_phone' => $rent->user_phone,
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
            'message' => 'Список аренд',
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
     * Получить информацию о конкретной аренде (для менеджеров)
     * GET /api/management/rents/{id}
     */
    public function show(int $id): JsonResponse
    {
        $rent = Rent::with('scooter')->findOrFail($id);

        return response()->json([
            'message' => 'Информация об аренде',
            'data' => [
                'id' => $rent->id,
                'user_phone' => $rent->user_phone,
                'scooter' => [
                    'id' => $rent->scooter->id,
                    'model' => $rent->scooter->model,
                    'status' => $rent->scooter->status,
                    'battery_level' => $rent->scooter->battery_level,
                ],
                'start_time' => $rent->start_time,
                'end_time' => $rent->end_time,
                'duration_minutes' => $rent->duration_in_minutes,
                'cost_rub' => $rent->cost,
                'status' => $rent->status,
                'created_at' => $rent->created_at,
                'updated_at' => $rent->updated_at,
            ],
        ]);
    }

    /**
     * Получить активную аренду ТЕКУЩЕГО пользователя
     * GET /api/rents/active
     * Требуется авторизация
     */
    public function active(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $phone = $user->phone;

        $activeRent = Rent::active()
            ->byPhone($phone)
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
     * Получить историю аренд ТЕКУЩЕГО пользователя
     * GET /api/rents/history
     * Требуется авторизация
     */
    public function history(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $phone = $user->phone;

        $rents = Rent::completed()
            ->byPhone($phone)
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

    /**
     * Получить статистику по арендам (для менеджеров)
     * GET /api/management/rents/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $totalRents = Rent::count();
        $activeRents = Rent::active()->count();
        $completedRents = Rent::completed()->count();
        
        $totalRevenue = Rent::completed()->get()->sum('cost');
        
        $avgDuration = Rent::completed()
            ->whereNotNull('end_time')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (end_time - start_time))/60) as avg_minutes')
            ->first()
            ->avg_minutes ?? 0;

        return response()->json([
            'message' => 'Статистика аренд',
            'data' => [
                'total_rents' => $totalRents,
                'active_rents' => $activeRents,
                'completed_rents' => $completedRents,
                'total_revenue_rub' => round($totalRevenue, 2),
                'average_duration_minutes' => round($avgDuration, 2),
                'current_time' => Carbon::now(),
            ],
        ]);
    }
}
