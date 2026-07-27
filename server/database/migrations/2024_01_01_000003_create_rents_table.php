<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->string('user_phone', 20); // Номер телефона арендатора
            $table->foreignId('scooter_id')->constrained('scooters')->onDelete('cascade'); // ID самоката
            $table->timestamp('start_time')->useCurrent(); // Время начала аренды
            $table->timestamp('end_time')->nullable(); // Время конца аренды
            $table->enum('status', ['active', 'completed'])->default('active'); // Статус аренды
            $table->timestamps();
            
            // Индексы для оптимизации запросов
            $table->index('user_phone');
            $table->index('status');
            $table->index('scooter_id');
            
            // Композитный индекс для поиска активных аренд по пользователю
            $table->index(['user_phone', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
