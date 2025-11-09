<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\ApiResponse;

class UserController extends Controller
{

    public function __construct(private UserService $userService) {}

    public function listUsers(Request $request)
    {
        try {
            $response = $this->userService->listUsers($request);

            return ApiResponse::success(
                data: $response['data'],
                pagination: $response['pagination'],
                message: 'List of users retrieved successfully'
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to retrieve user list';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 500,
                errors: $th->getMessage()
            );
        }
    }
}