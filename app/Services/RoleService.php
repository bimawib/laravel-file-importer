<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Requests\AssignRoleWebRequest;
use App\Http\Requests\UpdateRoleWebRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function listRoles(Request $request): array {
        $limit = $request->get('limit', 20);
        $search = $request->get('search');
        $sort = $request->get('sort', 'asc');

        $roles = Role::query()
            ->with('permissions:id,name')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name', $sort)
            ->paginate($limit);

        return [
            'data' => $roles->items(),
            'pagination' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total()
            ],
        ];
    }

    public function createRole(array $request): Role {
        $role = Role::create(['name' => $request['name']]);
        $role->syncPermissions($request['permissions']);
        return $role->load('permissions');
    }

    public function updateRole(array $request, Role $role): Role {
        $role->update(['name' => $request['name']]);
        $role->syncPermissions($request['permissions']);
        return $role->load('permissions');
    }

    public function assignRoleToUser(array $request): array {
        $user = User::where('email', $request['email'])->firstOrFail();
        $user->syncRoles($request['roles']);

        return [
            'user' => $user->email,
            'roles' => $user->roles->pluck('name')
        ];
    }
}