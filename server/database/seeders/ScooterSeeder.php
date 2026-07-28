<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scooter;
use Carbon\Carbon;

class ScooterSeeder extends Seeder
{
    /**
     * Города с координатами
     */
    private array $cities = [
        // Крым
        ['city' => 'Симферополь', 'lat' => 44.9521, 'lng' => 34.1024, 'count' => 15],
        ['city' => 'Севастополь', 'lat' => 44.6167, 'lng' => 33.5254, 'count' => 12],
        ['city' => 'Ялта', 'lat' => 44.4951, 'lng' => 34.1663, 'count' => 10],
        ['city' => 'Евпатория', 'lat' => 45.1904, 'lng' => 33.3666, 'count' => 8],
        ['city' => 'Керчь', 'lat' => 45.3573, 'lng' => 36.4682, 'count' => 7],
        ['city' => 'Феодосия', 'lat' => 45.0319, 'lng' => 35.3824, 'count' => 6],
        ['city' => 'Алушта', 'lat' => 44.6761, 'lng' => 34.4100, 'count' => 5],
        ['city' => 'Судак', 'lat' => 44.8500, 'lng' => 34.9667, 'count' => 4],
        ['city' => 'Бахчисарай', 'lat' => 44.7525, 'lng' => 33.8608, 'count' => 3],
        ['city' => 'Саки', 'lat' => 45.1342, 'lng' => 33.6033, 'count' => 3],
        
        // Краснодарский край
        ['city' => 'Краснодар', 'lat' => 45.0355, 'lng' => 38.9753, 'count' => 12],
        ['city' => 'Сочи', 'lat' => 43.5855, 'lng' => 39.7231, 'count' => 10],
        ['city' => 'Анапа', 'lat' => 44.8947, 'lng' => 37.3167, 'count' => 6],
        ['city' => 'Геленджик', 'lat' => 44.5630, 'lng' => 38.0790, 'count' => 5],
        ['city' => 'Новороссийск', 'lat' => 44.7237, 'lng' => 37.7688, 'count' => 5],
        ['city' => 'Туапсе', 'lat' => 44.1000, 'lng' => 39.0833, 'count' => 4],
        ['city' => 'Ейск', 'lat' => 46.7100, 'lng' => 38.2700, 'count' => 3],
        
        // Ростовская область
        ['city' => 'Ростов-на-Дону', 'lat' => 47.2225, 'lng' => 39.7187, 'count' => 10],
        ['city' => 'Таганрог', 'lat' => 47.2362, 'lng' => 38.8967, 'count' => 5],
        ['city' => 'Азов', 'lat' => 47.1000, 'lng' => 39.4333, 'count' => 3],
        ['city' => 'Батайск', 'lat' => 47.1333, 'lng' => 39.7500, 'count' => 3],
        ['city' => 'Волгодонск', 'lat' => 47.5167, 'lng' => 42.1500, 'count' => 3],
        ['city' => 'Новочеркасск', 'lat' => 47.4167, 'lng' => 40.0833, 'count' => 2],
    ];

    /**
     * Модели самокатов
     */
    private array $models = [
        'Ninebot Max G30',
        'Ninebot Max G30LP',
        'Ninebot ES2',
        'Ninebot ES4',
        'Ninebot F40',
        'Ninebot F65',
        'Xiaomi Mi 3',
        'Xiaomi Pro 2',
        'Xiaomi Essential',
        'Xiaomi Mi 1S',
        'Kugoo S3',
        'Kugoo S3 Pro',
        'Kugoo M4 Pro',
        'Kugoo Kirin G3',
        'Halo Knight T104',
        'Dualtron Mini',
        'Dualtron Spider',
        'Kaabo Mantis 8',
        'Kaabo Wolf Warrior',
        'Zero 10X',
    ];

    /**
     * Возможные статусы
     */
    private array $statuses = [
        'available',
        'available',
        'available',
        'available',
        'available',
        'in_use',
        'in_use',
        'maintenance',
        'maintenance',
        'offline',
    ];

    public function run(): void
    {
        // Очищаем таблицу перед заполнением
        Scooter::truncate();
        
        $scooters = [];
        $scooterId = 1;

        foreach ($this->cities as $cityData) {
            for ($i = 0; $i < $cityData['count']; $i++) {
                // Случайное смещение координат (до 500 метров)
                $latOffset = (mt_rand(-500, 500) / 100000); // примерно 5 метров = 0.00005 градуса
                $lngOffset = (mt_rand(-500, 500) / 100000);

                // Случайный уровень заряда с распределением
                $batteryRand = mt_rand(1, 100);
                if ($batteryRand <= 60) {
                    $battery = mt_rand(70, 100); // 60% самокатов с зарядом 70-100%
                } elseif ($batteryRand <= 85) {
                    $battery = mt_rand(30, 69);  // 25% самокатов с зарядом 30-69%
                } else {
                    $battery = mt_rand(0, 29);   // 15% самокатов с зарядом 0-29%
                }

                // Случайный статус (больше available)
                $status = $this->statuses[array_rand($this->statuses)];

                // Если заряд ниже 20%, статус не может быть available
                if ($battery < 20 && $status === 'available') {
                    $status = 'maintenance';
                }

                // Если заряд 0, статус только maintenance или offline
                if ($battery === 0 && !in_array($status, ['maintenance', 'offline'])) {
                    $status = mt_rand(0, 1) ? 'maintenance' : 'offline';
                }

                // Случайная дата обновления (последние 7 дней)
                $lastUpdated = Carbon::now()->subDays(mt_rand(0, 7))
                    ->subHours(mt_rand(0, 23))
                    ->subMinutes(mt_rand(0, 59));

                $scooters[] = [
                    'model' => $this->models[array_rand($this->models)],
                    'status' => $status,
                    'battery_level' => $battery,
                    'latitude' => round($cityData['lat'] + $latOffset, 7),
                    'longitude' => round($cityData['lng'] + $lngOffset, 7),
                    'last_updated' => $lastUpdated,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                echo "Создан самокат #{$scooterId}: {$cityData['city']} - " . 
                     $this->models[array_rand($this->models)] . 
                     " [{$status}] 🔋{$battery}%\n";
                
                $scooterId++;
            }
        }

        // Массовая вставка
        foreach (array_chunk($scooters, 50) as $chunk) {
            Scooter::insert($chunk);
        }

        echo "\n✅ Всего создано самокатов: " . count($scooters) . "\n";
        
        // Статистика
        $this->showStats();
    }

    private function showStats(): void
    {
        echo "\n═══════════════════════════════════\n";
        echo "📊 СТАТИСТИКА СОЗДАННЫХ САМОКАТОВ\n";
        echo "═══════════════════════════════════\n\n";

        // По городам
        echo "📍 По городам:\n";
        foreach ($this->cities as $city) {
            $count = Scooter::whereBetween('latitude', [$city['lat'] - 0.01, $city['lat'] + 0.01])
                ->whereBetween('longitude', [$city['lng'] - 0.01, $city['lng'] + 0.01])
                ->count();
            echo "  {$city['city']}: {$count} шт.\n";
        }

        // По статусам
        echo "\n📌 По статусам:\n";
        $statuses = Scooter::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
        foreach ($statuses as $status) {
            $emoji = match($status->status) {
                'available' => '🟢',
                'in_use' => '🔵',
                'maintenance' => '🟡',
                'offline' => '⚫',
                default => '❓'
            };
            echo "  {$emoji} {$status->status}: {$status->count} шт.\n";
        }

        // По заряду
        $avgBattery = Scooter::avg('battery_level');
        $minBattery = Scooter::min('battery_level');
        $maxBattery = Scooter::max('battery_level');
        
        echo "\n🔋 Заряд батарей:\n";
        echo "  Средний: " . round($avgBattery, 1) . "%\n";
        echo "  Минимальный: {$minBattery}%\n";
        echo "  Максимальный: {$maxBattery}%\n";

        $total = Scooter::count();
        echo "\n📦 Всего самокатов: {$total}\n";
        echo "═══════════════════════════════════\n";
    }
}
