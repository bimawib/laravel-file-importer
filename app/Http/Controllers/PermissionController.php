<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePermissionWebRequest;
use App\Http\Requests\UpdatePermissionWebRequest;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\ApiResponse;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $permissionService) {}


    public function listPermissions(Request $request) {
        try {
            $result = $this->permissionService->listPermissions($request);

            return ApiResponse::success(
                data: $result['data'],
                pagination: $result['pagination'],
                message: 'List of permissions'
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to retrieve permissions list';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 500,
                errors: $th->getMessage()
            );
        }
    }

    public function createPermission(CreatePermissionWebRequest $request) {
        try {
            $permission = $this->permissionService->createPermission($request->input('name'));

            return ApiResponse::success(
                data: $permission,
                message: 'Permission created successfully',
                status: 201
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to create permission';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 400,
                errors: $th->getMessage()
            );
        }
    }

    public function updatePermission(UpdatePermissionWebRequest $request, Permission $permission) {
        try {
            $request->validated();
            $updated = $this->permissionService->updatePermission($permission, $request->input('name'));

            return ApiResponse::success(
                data: $updated,
                message: 'Permission updated successfully'
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to update permission';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error(
                message: $errorMsg,
                status: 400,
                errors: $th->getMessage()
            );
        }
    }
}
