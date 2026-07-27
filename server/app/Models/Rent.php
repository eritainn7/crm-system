<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Rent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_phone',
        'scooter_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Статусы аренды
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';

    // Связь с самокатом
    public function scooter(): BelongsTo
    {
        return $this->belongsTo(Scooter::class);
    }

    // Scope для активных аренд
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope для завершённых аренд
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Scope для аренд пользователя
    public function scopeByPhone($query, string $phone)
    {
        return $query->where('user_phone', $phone);
    }

    // Рассчёт длительности аренды в минутах
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->end_time) {
            return null;
        }
        return $this->start_time->diffInMinutes($this->end_time);
    }

    // Рассчёт стоимости аренды (пример: 5 рублей в минуту)
    public function getCostAttribute(): ?float
    {
        $minutes = $this->duration_in_minutes;
        if ($minutes === null) {
            return null;
        }
        return round($minutes * 5, 2); // 5 руб/мин
    }

    // Завершение аренды
    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'end_time' => Carbon::now(),
        ]);
        
        // Возвращаем самокат в доступные
        $this->scooter->update(['status' => Scooter::STATUS_AVAILABLE]);
    }

    // Проверка, активна ли аренда
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
