<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scooters', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100); // Модель самоката
            $table->enum('status', ['available', 'in_use', 'maintenance', 'offline'])->default('available');
            $table->integer('battery_level')->unsigned()->default(100); // 0-100%
            $table->decimal('latitude', 10, 8); // Широта
            $table->decimal('longitude', 11, 8); // Долгота
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index('status');
            $table->index('battery_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scooters');
    }
};
