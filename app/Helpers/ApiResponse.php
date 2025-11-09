<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        ?array $pagination = null
    ): JsonResponse {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'errors' => null,
            'data' => $data,
            'pagination' => $pagination
        ], $status);
    }

    public static function error(
        string $message = 'Something went wrong',
        int $status = 500,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
            'data' => null,
            'pagination' => null
        ], $status);
    }
}
