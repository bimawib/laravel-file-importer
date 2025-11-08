<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request) {
        return $this->authService->login($request->validated());
    }

    public function userInfo() {
        return $this->authService->currentUser();
    }

    public function logout() {
        return $this->authService->logout();
    }
}
