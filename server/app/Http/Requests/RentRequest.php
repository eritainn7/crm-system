<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'scooter_id' => 'required|integer|exists:scooters,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_phone.required' => 'Номер телефона обязателен',
            'user_phone.regex' => 'Неверный формат номера телефона',
            'scooter_id.required' => 'ID самоката обязателен',
            'scooter_id.exists' => 'Самокат не найден',
        ];
    }
}
