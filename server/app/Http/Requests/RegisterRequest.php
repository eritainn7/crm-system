<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|regex:/^\+?[0-9]{11}$/',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'ФИО обязательно',
            'phone.required' => 'Номер телефона обязателен',
            'phone.unique' => 'Этот номер уже зарегистрирован',
            'phone.regex' => 'Неверный формат телефона',
            'password.required' => 'Пароль обязателен',
            'password.min' => 'Минимум 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
