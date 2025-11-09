<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    public function login(array $credentials): array {
        if (!$token = auth('api')->attempt($credentials)) {
            abort(401, 'Invalid credentials');
        }

        $expiresIn = auth('api')->factory()->getTTL() * 60;

        $cookie = cookie(
            name: 'access_token',
            value: $token,
            minutes: $expiresIn / 60,
            path: '/',
            domain: null,
            secure: app()->environment('production'),
            httpOnly: app()->environment('production'),
            raw: false,
            sameSite: 'Strict'
        );

        return [
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiresIn,
            ],
            'cookie' => $cookie
        ];
    }

    public function logout() {
        auth('api')->logout();
    }

    public function userInfo() {
        return auth('api')->user();
    }
}
