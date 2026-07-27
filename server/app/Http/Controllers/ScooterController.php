<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScooterRequest;
use App\Models\Scooter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScooterController extends Controller
{
    /**
     * Получить список всех самокатов с фильтрацией
     * GET /api/management/scooters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Scooter::query();

        // Фильтрация по статусу
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Фильтрация по уровню заряда
        if ($request->has('min_battery')) {
            $query->where('battery_level', '>=', $request->min_battery);
        }

        // Поиск по модели
        if ($request->has('model')) {
            $query->where('model', 'ILIKE', '%' . $request->model . '%');
        }

        // Сортировка
        $sortField = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'asc');
        $allowedSortFields = ['id', 'model', 'status', 'battery_level', 'last_updated', 'created_at'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $scooters = $query->paginate($perPage);

        return response()->json([
            'message' => 'Список самокатов',
            'data' => $scooters->items(),
            'pagination' => [
                'current_page' => $scooters->currentPage(),
                'last_page' => $scooters->lastPage(),
                'per_page' => $scooters->perPage(),
                'total' => $scooters->total(),
            ],
        ]);
    }

    /**
     * Получить информацию о конкретном самокате
     * GET /api/management/scooters/{id}
     */
    public function show(int $id): JsonResponse
    {
        $scooter = Scooter::findOrFail($id);

        return response()->json([
            'message' => 'Информация о самокате',
            'data' => $scooter,
        ]);
    }

    /**
     * Создать новый самокат
     * POST /api/management/scooters
     */
    public function store(ScooterRequest $request): JsonResponse
    {
        $scooter = Scooter::create([
            'model' => $request->model,
            'status' => $request->status,
            'battery_level' => $request->battery_level,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'last_updated' => $request->last_updated ?? now(),
        ]);

        return response()->json([
            'message' => 'Самокат создан',
            'data' => $scooter,
        ], 201);
    }

    /**
     * Обновить информацию о самокате
     * PUT /api/management/scooters/{id}
     */
    public function update(ScooterRequest $request, int $id): JsonResponse
    {
        $scooter = Scooter::findOrFail($id);

        $updateData = $request->only([
            'model',
            'status',
            'battery_level',
            'latitude',
            'longitude',
        ]);

        // Всегда обновляем дату последнего обновления
        $updateData['last_updated'] = now();

        $scooter->update($updateData);

        return response()->json([
            'message' => 'Самокат обновлён',
            'data' => $scooter->fresh(),
        ]);
    }

    /**
     * Удалить самокат
     * DELETE /api/management/scooters/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $scooter = Scooter::findOrFail($id);
        $scooter->delete();

        return response()->json([
            'message' => 'Самокат удалён',
        ]);
    }

    /**
     * Получить список доступных самокатов (публичный)
     * GET /api/scooters/available
     */
    public function available(Request $request): JsonResponse
    {
        $query = Scooter::available();

        // Поиск поблизости если переданы координаты
        if ($request->has('latitude') && $request->has('longitude')) {
            $radius = $request->get('radius', 5); // радиус в км
            $query->nearby(
                $request->latitude,
                $request->longitude,
                $radius
            );
        }

        $scooters = $query->get();

        return response()->json([
            'message' => 'Доступные самокаты',
            'count' => $scooters->count(),
            'data' => $scooters,
        ]);
    }

    /**
     * Массовое обновление статуса самокатов
     * POST /api/management/scooters/batch-status
     */
    public function batchUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:scooters,id',
            'status' => 'required|string|in:' . implode(',', Scooter::getStatuses()),
        ]);

        $count = Scooter::whereIn('id', $request->ids)
            ->update([
                'status' => $request->status,
                'last_updated' => now(),
            ]);

        return response()->json([
            'message' => "Обновлено самокатов: {$count}",
            'updated_count' => $count,
        ]);
    }
}
