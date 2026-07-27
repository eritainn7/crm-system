<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scooter;
use Carbon\Carbon;

class ScooterSeeder extends Seeder
{
    public function run(): void
    {
        $scooters = [
            [
                'model' => 'Ninebot Max G30',
                'status' => 'available',
                'battery_level' => 95,
                'latitude' => 55.7558,
                'longitude' => 37.6173,
                'last_updated' => Carbon::now(),
            ],
            [
                'model' => 'Xiaomi Mi 3',
                'status' => 'in_use',
                'battery_level' => 45,
                'latitude' => 55.7539,
                'longitude' => 37.6208,
                'last_updated' => Carbon::now(),
            ],
            [
                'model' => 'Kugoo S3',
                'status' => 'available',
                'battery_level' => 80,
                'latitude' => 55.7580,
                'longitude' => 37.6150,
                'last_updated' => Carbon::now(),
            ],
            [
                'model' => 'Ninebot ES2',
                'status' => 'maintenance',
                'battery_level' => 0,
                'latitude' => 55.7600,
                'longitude' => 37.6180,
                'last_updated' => Carbon::now(),
            ],
            [
                'model' => 'Xiaomi Pro 2',
                'status' => 'offline',
                'battery_level' => 60,
                'latitude' => 55.7570,
                'longitude' => 37.6190,
                'last_updated' => Carbon::now(),
            ],
        ];

        foreach ($scooters as $scooter) {
            Scooter::create($scooter);
        }
    }
}
