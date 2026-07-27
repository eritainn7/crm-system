<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Scooter;

class ScooterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'model' => 'required|string|max:100',
            'status' => 'required|string|in:' . implode(',', Scooter::getStatuses()),
            'battery_level' => 'required|integer|min:0|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'last_updated' => 'nullable|date',
        ];

        // Для обновления делаем поля необязательными
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['model'] = 'sometimes|string|max:100';
            $rules['status'] = 'sometimes|string|in:' . implode(',', Scooter::getStatuses());
            $rules['battery_level'] = 'sometimes|integer|min:0|max:100';
            $rules['latitude'] = 'sometimes|numeric|between:-90,90';
            $rules['longitude'] = 'sometimes|numeric|between:-180,180';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'model.required' => 'Модель самоката обязательна',
            'status.required' => 'Статус обязателен',
            'status.in' => 'Недопустимый статус самоката',
            'battery_level.required' => 'Уровень заряда обязателен',
            'battery_level.min' => 'Заряд не может быть меньше 0%',
            'battery_level.max' => 'Заряд не может быть больше 100%',
            'latitude.required' => 'Широта обязательна',
            'latitude.between' => 'Широта должна быть от -90 до 90',
            'longitude.required' => 'Долгота обязательна',
            'longitude.between' => 'Долгота должна быть от -180 до 180',
        ];
    }
}
