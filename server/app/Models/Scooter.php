<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scooter extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'status',
        'battery_level',
        'latitude',
        'longitude',
        'last_updated',
    ];

    protected $casts = [
        'battery_level' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'last_updated' => 'datetime',
    ];

    // Доступные статусы
    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in_use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_OFFLINE = 'offline';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_IN_USE,
            self::STATUS_MAINTENANCE,
            self::STATUS_OFFLINE,
        ];
    }

    // Scope для доступных самокатов
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                     ->where('battery_level', '>', 20); // Только с зарядом > 20%
    }

    // Scope для самокатов поблизости
    public function scopeNearby($query, $latitude, $longitude, $radius = 5)
    {
        // Приблизительный расчёт расстояния (в километрах)
        return $query->selectRaw("
            *, 
            (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
    }
}
