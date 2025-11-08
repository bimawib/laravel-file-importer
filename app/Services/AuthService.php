<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    public function login(array $credentials) {
        try {
            if (!$token = auth('api')->attempt($credentials)) {
                return ApiResponse::error('Invalid Credentials', 401);
            }

            return ApiResponse::success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            return ApiResponse::error('Could not create token', 500);
        }
    }

    public function logout() {
        auth('api')->logout();
        return ApiResponse::success(true, 'User has been logged out');
    }

    public function currentUser() {
        return ApiResponse::success(auth('api')->user());
    }
}
