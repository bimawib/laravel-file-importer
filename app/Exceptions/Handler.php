<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception) {
        if ($exception instanceof UnauthorizedException) {
            Log::warning('Permission denied', [
                'user_id' => auth()->id(),
                'required' => $exception->getRequiredPermissions(),
                'message' => $exception->getMessage()
            ]);

            return ApiResponse::error(
                message: 'Forbidden: User does not have the right permissions.',
                status: 403,
                errors: $exception->getMessage()
            );
        }

        if ($exception instanceof ValidationException) {
            Log::warning('Validation failed', ['errors' => $exception->errors()]);

            return ApiResponse::error(
                message: 'Validation failed',
                status: 422,
                errors: $exception->errors()
            );
        }

        if ($exception instanceof UnauthorizedHttpException) {
            Log::warning('Unauthorized access attempt', ['error' => $exception->getMessage()]);

            return ApiResponse::error(
                message: 'Token not provided or invalid',
                status: 401,
                errors: $exception->getMessage()
            );
        }

        if ($exception instanceof TokenExpiredException) {
            Log::warning('JWT token expired', ['error' => $exception->getMessage()]);

            return ApiResponse::error(
                message: 'Token has expired',
                status: 401,
                errors: $exception->getMessage()
            );
        }

        if ($exception instanceof TokenInvalidException) {
            Log::warning('JWT token invalid', ['error' => $exception->getMessage()]);

            return ApiResponse::error(
                message: 'Invalid token',
                status: 401,
                errors: $exception->getMessage()
            );
        }

        if ($exception instanceof JWTException) {
            Log::error('JWT general error', ['error' => $exception->getMessage()]);

            return ApiResponse::error(
                message: 'JWT error',
                status: 401,
                errors: $exception->getMessage()
            );
        }
        
        Log::error('Unexpected exception', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return ApiResponse::error(
            message: 'Unexpected server error',
            status: 500,
            errors: config('app.debug') ? $exception->getMessage() : 'Internal Server Error'
        );
    }
}
