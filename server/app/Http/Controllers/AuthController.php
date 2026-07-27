<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Регистрация: POST /api/auth/reg
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Регистрация успешна',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Вход: POST /api/auth/log
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'message' => 'Неверный номер или пароль',
            ], 401);
        }

        $user = User::where('phone', $request->phone)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Вход выполнен',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Выход: POST /api/auth/out
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Выход выполнен',
        ]);
    }
}