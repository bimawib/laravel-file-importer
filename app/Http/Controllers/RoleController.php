<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleWebRequest;
use App\Http\Requests\CreateRoleWebRequest;
use App\Http\Requests\UpdateRoleWebRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    public function listRoles(Request $request) {
        try {
            $result = $this->roleService->listRoles($request);

            return ApiResponse::success(
                data: $result['data'],
                pagination: $result['pagination'],
                message: 'List of roles with permissions'
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to retrieve roles list';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error($errorMsg, 500, $th->getMessage());
        }
    }
    public function createRole(CreateRoleWebRequest $request) {
        try {
            $role = $this->roleService->createRole($request->validated());

            return ApiResponse::success(
                data: $role,
                message: 'Role created successfully',
                status: 201
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to create role';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error($errorMsg, 400, $th->getMessage());
        }
    }

    public function updateRole(UpdateRoleWebRequest $request, Role $role) {
        try {
            $updatedRole = $this->roleService->updateRole($request->validated(), $role);

            return ApiResponse::success(
                data: $updatedRole,
                message: 'Role updated successfully',
                status: 200
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to update role';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error($errorMsg, 400, $th->getMessage());
        }
    }

    public function assignRoleToUser(AssignRoleWebRequest $request) {
        try {
            $result = $this->roleService->assignRoleToUser($request->validated());

            return ApiResponse::success(
                data: $result,
                message: 'User role has been updated',
                status: 200
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to update user role';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error($errorMsg, 400, $th->getMessage());
        }
    }
}
