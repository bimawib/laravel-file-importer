<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle($request, Closure $next) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return ApiResponse::error(
                message: 'Token has expired', 
                status: Response::HTTP_UNAUTHORIZED
            );
        } catch (TokenBlacklistedException $e) {
            return ApiResponse::error(
                message: 'Token has been revoked (user logged out)', 
                status: Response::HTTP_UNAUTHORIZED
            );
        } catch (TokenInvalidException $e) {
            return ApiResponse::error(
                message: 'Invalid token', 
                status: Response::HTTP_UNAUTHORIZED
            );
        } catch (JWTException $e) {
            return ApiResponse::error(
                message: 'Token missing or malformed', 
                status: Response::HTTP_UNAUTHORIZED
            );
        } catch (Exception $e) {
            return ApiResponse::error(
                message: 'Unauthorized access', 
                status: Response::HTTP_UNAUTHORIZED
            );
        }

        return $next($request);
    }
}
