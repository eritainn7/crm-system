<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Номер телефона обязателен',
            'phone.exists' => 'Пользователь не найден',
            'password.required' => 'Пароль обязателен',
        ];
    }
}
