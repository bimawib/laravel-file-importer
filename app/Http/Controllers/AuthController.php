<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterNewUserRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\AuthService;
use App\Services\UserService;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService
    ) {}

    public function login(LoginRequest $request) {
        try {
            $response = $this->authService->login($request->validated());

            return ApiResponse::success(
                data: $response['data'],
                message: 'Login successful',
                status: 200
            )->withCookie($response['cookie']);
        } catch (JWTException $e) {
            $errorMsg = 'Failed to generate JWT token';
            Log::error($errorMsg, ['error' => $e->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 500,
                errors: $e->getMessage()
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to log in';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: $th->getCode() ?: 500,
                errors: $th->getMessage()
            );
        }
    }

    public function userInfo() {
        try {
            $user = $this->authService->userInfo();

            return ApiResponse::success(
                data: $user,
                message: 'User information retrieved successfully'
            );

        } catch (\Throwable $th) {
            $errorMsg = 'Failed to retrieve user info';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 500,
                errors: $th->getMessage()
            );
        }
    }

    public function logout() {
        try {
            $this->authService->logout();

            return ApiResponse::success(
                data: true,
                message: 'User has been logged out'
            );

        } catch (JWTException $e) {
            $errorMsg = 'Invalid or missing token';
            Log::warning($errorMsg, ['error' => $e->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 401,
                errors: $e->getMessage()
            );

        } catch (\Throwable $th) {
            $errorMsg = 'Unexpected error during logout';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 500,
                errors: $th->getMessage()
            );
        }
    }

    public function register(RegisterNewUserRequest $request) {
        try {
            $request->validated();
            $user = $this->userService->register($request);

            return ApiResponse::success(
                data: $user,
                message: 'User registered successfully',
                status: 200
            );

        } catch (\Throwable $th) {
            $errorMsg = 'Failed to register user';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 500,
                errors: $th->getMessage()
            );
        }
    }
}
