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
            'scooter_id' => 'required|integer|exists:scooters,id',
        ];
    }

    public function messages(): array
    {
        return [
            'scooter_id.required' => 'ID самоката обязателен',
            'scooter_id.exists' => 'Самокат не найден',
        ];
    }
}